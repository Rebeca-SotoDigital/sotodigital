<?php
declare(strict_types=1);

require_once __DIR__ . '/lead-extractor.php';
require_once __DIR__ . '/lead-validator.php';
require_once __DIR__ . '/lead-store.php';

const SOTO_LEAD_CONFIRMACION = 'CONFIRMAR PROPUESTA';
const SOTO_LEAD_TTL = 48 * 60 * 60;

/**
 * Directorio privado de borradores.
 * En producción se configurará fuera del directorio web.
 */
function pipelineDirectory(): string
{
    $dir = getenv('SOTO_PIPELINE_DIR');

    if (!$dir) {
        $dir = sys_get_temp_dir()
            . '/soto-digital-pipeline-dev';
    }

    if (!is_dir($dir) &&
        !mkdir($dir, 0700, true) &&
        !is_dir($dir)) {
        throw new RuntimeException(
            'No se pudo crear el directorio del pipeline'
        );
    }

    if (!is_writable($dir)) {
        throw new RuntimeException(
            'Directorio del pipeline no escribible'
        );
    }

    return $dir;
}

/**
 * Lee y actualiza el estado de un cliente con bloqueo.
 */

/**
 * Lee y actualiza el pipeline bajo bloqueo.
 *
 * Utiliza un archivo .lock independiente
 * y una escritura atómica del estado JSON.
 */
function pipelineEstado(
    string $phone,
    callable $operacion
): mixed {
    if (!preg_match('/^[0-9]{8,16}$/', $phone)) {
        throw new InvalidArgumentException(
            'Identificador de cliente no válido'
        );
    }

    $directory = pipelineDirectory();
    $path = $directory . '/' .
        hash('sha256', $phone) . '.json';

    $lockPath = $path . '.lock';

    $oldUmask = umask(0077);

    try {
        $lock = fopen($lockPath, 'c');
    } finally {
        umask($oldUmask);
    }

    if ($lock === false) {
        throw new RuntimeException(
            'No se pudo abrir el bloqueo del pipeline'
        );
    }

    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException(
                'No se pudo bloquear el pipeline'
            );
        }

        $state = [];

        if (is_file($path)) {
            $raw = file_get_contents($path);

            if ($raw === false || trim($raw) === '') {
                throw new RuntimeException(
                    'Pipeline vacío o ilegible'
                );
            }

            try {
                $state = json_decode(
                    $raw,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (JsonException $e) {
                throw new RuntimeException(
                    'Pipeline corrupto',
                    0,
                    $e
                );
            }

            if (!is_array($state)) {
                throw new RuntimeException(
                    'Estado del pipeline inválido'
                );
            }
        }

        $result = $operacion($state);

        if (
            !is_array($result) ||
            !array_key_exists('resultado', $result)
        ) {
            throw new RuntimeException(
                'Operación del pipeline inválida'
            );
        }

        if (empty($result['guardar'])) {
            return $result['resultado'];
        }

        if (
            !isset($result['estado']) ||
            !is_array($result['estado'])
        ) {
            throw new RuntimeException(
                'Nuevo estado del pipeline inválido'
            );
        }

        $json = json_encode(
            $result['estado'],
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        );

        $temp = tempnam(
            $directory,
            '.pipeline-'
        );

        if ($temp === false) {
            throw new RuntimeException(
                'No se pudo crear archivo temporal'
            );
        }

        try {
            if (!chmod($temp, 0600)) {
                throw new RuntimeException(
                    'No se pudieron proteger los permisos'
                );
            }

            $handle = fopen($temp, 'wb');

            if ($handle === false) {
                throw new RuntimeException(
                    'No se pudo abrir el temporal'
                );
            }

            try {
                $length = strlen($json);
                $written = 0;

                while ($written < $length) {
                    $n = fwrite(
                        $handle,
                        substr($json, $written)
                    );

                    if ($n === false || $n === 0) {
                        throw new RuntimeException(
                            'Escritura incompleta'
                        );
                    }

                    $written += $n;
                }

                if (!fflush($handle)) {
                    throw new RuntimeException(
                        'Error vaciando el búfer'
                    );
                }

                if (
                    function_exists('fsync') &&
                    !fsync($handle)
                ) {
                    throw new RuntimeException(
                        'Error sincronizando el pipeline'
                    );
                }
            } finally {
                fclose($handle);
            }

            if (file_get_contents($temp) !== $json) {
                throw new RuntimeException(
                    'Verificación de escritura fallida'
                );
            }

            if (!rename($temp, $path)) {
                throw new RuntimeException(
                    'No se pudo publicar el pipeline'
                );
            }

        } finally {
            if (is_file($temp)) {
                unlink($temp);
            }
        }

        return $result['resultado'];

    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}


/**
 * Extrae los datos de la conversación y prepara
 * una solicitud pendiente de confirmación.
 */
function pipelinePreparar(
    string $phone,
    array $historial
): array {
    $datos = extraerLead($historial);

    if (($datos['solicita_propuesta'] ?? false) !== true) {
        return [
            'estado' => 'sin_interes',
            'mensaje' => 'El cliente no ha solicitado una propuesta.'
        ];
    }

    $nombre = trim($datos['nombre'] ?? '');
    $negocio = trim($datos['negocio'] ?? '');
    $email = trim($datos['email'] ?? '');
    $necesidad = trim($datos['necesidad'] ?? '');

    $faltan = [];

    if ($nombre === '' || mb_strlen($nombre) > 160) {
        $faltan[] = 'nombre';
    }

    if ($negocio === '' || mb_strlen($negocio) > 200) {
        $faltan[] = 'negocio';
    }

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 254
    ) {
        $faltan[] = 'email';
    }

    if (
        $necesidad === '' ||
        mb_strlen($necesidad) > 2000
    ) {
        $faltan[] = 'necesidad';
    }

    if ($faltan) {
        return [
            'estado' => 'incompleto',
            'faltan' => $faltan,
            'mensaje' => 'Faltan datos antes de pedir confirmación.'
        ];
    }

    $borrador = [
        'nombre' => $nombre,
        'negocio' => $negocio,
        'email' => $email,
        'necesidad' => $necesidad,
        'solicita_propuesta' => true
    ];

    pipelineEstado(
        $phone,
        function (array $state) use ($borrador): array {
            return [
                'guardar' => true,
                'estado' => [
                    'fase' => 'pendiente_confirmacion',
                    'creado_en' => time(),
                    'datos' => $borrador
                ],
                'resultado' => null
            ];
        }
    );

    $resumen =
        "Perfecto. Vamos a preparar tu valoración gratuita.\n\n" .
        "Estos son los datos de tu solicitud:\n\n" .
        "Nombre: {$nombre}\n" .
        "Negocio: {$negocio}\n" .
        "Email: {$email}\n" .
        "Necesidad: {$necesidad}\n\n" .
        "Utilizaremos estos datos para atender tu " .
        "solicitud y contactar contigo por email. " .
        "Puedes consultar nuestra política de privacidad en " .
        "https://soto-digital.com/.\n\n" .
        "Si los datos son correctos y aceptas que los " .
        "utilicemos para gestionar esta solicitud, " .
        "responde exactamente:\n\n" .
        SOTO_LEAD_CONFIRMACION . "\n\n" .
        "Si necesitas corregir algo, indícanoslo.";

    return [
        'estado' => 'pendiente_confirmacion',
        'mensaje' => $resumen
    ];
}

/**
 * Solo confirma un borrador ya presentado al cliente.
 * La integración de WhatsApp deberá comprobar que
 * el resumen fue realmente entregado antes de
 * aceptar la confirmación.
 */
function pipelineConfirmar(
    string $phone,
    string $textoCliente
): array {
    if (trim($textoCliente) !== SOTO_LEAD_CONFIRMACION) {
        return [
            'estado' => 'sin_confirmacion',
            'mensaje' => 'No se ha confirmado la propuesta.'
        ];
    }

    return pipelineEstado(
        $phone,
        function (array $state) use ($phone): array {
            if (($state['fase'] ?? '') === 'registrado') {
                return [
                    'guardar' => false,
                    'resultado' => [
                        'estado' => 'registrado',
                        'mensaje' =>
                            'Esta solicitud ya estaba registrada.'
                    ]
                ];
            }

            if (
                ($state['fase'] ?? '') !==
                'pendiente_confirmacion'
            ) {
                return [
                    'guardar' => false,
                    'resultado' => [
                        'estado' => 'sin_borrador',
                        'mensaje' =>
                            'No existe una propuesta pendiente.'
                    ]
                ];
            }

            $creado = (int) ($state['creado_en'] ?? 0);

            if (
                $creado <= 0 ||
                time() - $creado > SOTO_LEAD_TTL
            ) {
                return [
                    'guardar' => true,
                    'estado' => [
                        'fase' => 'caducado'
                    ],
                    'resultado' => [
                        'estado' => 'caducado',
                        'mensaje' =>
                            'La solicitud ha caducado. ' .
                            'Debe prepararse nuevamente.'
                    ]
                ];
            }

            $datos = $state['datos'] ?? [];

            $validacion = validarLeadExtraido(
                $datos,
                true,
                true
            );

            if (!$validacion['valido']) {
                return [
                    'guardar' => false,
                    'resultado' => [
                        'estado' => 'no_valido',
                        'errores' => $validacion['errores']
                    ]
                ];
            }

            // Solo después de confirmar y validar.
            $lead = guardarLead(
                $phone,
                array_merge(
                    $validacion['datos'],
                    [
                        'confirmado' => true,
                        'consentimiento' => true
                    ]
                )
            );

            return [
                'guardar' => true,
                'estado' => [
                    'fase' => 'registrado',
                    'registrado_en' => time(),
                    'lead_id' => $lead['id']
                ],
                'resultado' => [
                    'estado' => 'registrado',
                    'lead_id' => $lead['id'],
                    'mensaje' =>
                        '¡Gracias! Hemos registrado tu solicitud. ' .
                        'El equipo de Soto Digital revisará ' .
                        'tu información para preparar la propuesta.'
                ]
            ];
        }
    );
}
