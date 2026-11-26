<?php
declare(strict_types=1);

/**
 * Almacenamiento de leads - Soto Digital
 * Versión inicial para pruebas locales.
 */

function leadDirectory(): string
{
    $directory = getenv('SOTO_LEADS_DIR');

    if (!$directory) {
        $directory = sys_get_temp_dir()
            . '/soto-digital-leads-dev';
    }

    if (!is_dir($directory)) {
        if (
            !mkdir($directory, 0700, true) &&
            !is_dir($directory)
        ) {
            throw new RuntimeException(
                'No se puede crear el directorio de leads'
            );
        }
    }

    if (!is_writable($directory)) {
        throw new RuntimeException(
            'El directorio no permite escritura'
        );
    }

    return $directory;
}

/**
 * Registra un lead confirmado por el cliente.
 *
 * No llamar a esta función únicamente porque
 * Claude detecte interés comercial.
 */

/**
 * Guarda un lead confirmado e impide duplicados.
 *
 * Un lead por teléfono en esta versión.
 * La escritura se hace mediante archivo temporal.
 */
function guardarLead(
    string $clienteId,
    array $datos
): array {
    if (!preg_match('/^[0-9]{8,16}$/', $clienteId)) {
        throw new InvalidArgumentException(
            'Identificador de cliente no válido'
        );
    }

    if (
        ($datos['confirmado'] ?? false) !== true ||
        ($datos['consentimiento'] ?? false) !== true
    ) {
        throw new InvalidArgumentException(
            'Falta confirmación o consentimiento'
        );
    }

    $nombre = trim((string) ($datos['nombre'] ?? ''));
    $negocio = trim((string) ($datos['negocio'] ?? ''));
    $email = trim((string) ($datos['email'] ?? ''));
    $necesidad = trim((string) ($datos['necesidad'] ?? ''));

    if (
        $nombre === '' ||
        $negocio === '' ||
        $necesidad === '' ||
        mb_strlen($nombre) > 160 ||
        mb_strlen($negocio) > 200 ||
        mb_strlen($necesidad) > 2000 ||
        strlen($email) > 254 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        throw new InvalidArgumentException(
            'Datos comerciales no válidos'
        );
    }

    $campos = [
        'nombre' => $nombre,
        'negocio' => $negocio,
        'email' => $email,
        'necesidad' => $necesidad
    ];

    $hash = hash('sha256', $clienteId);
    $directory = leadDirectory();
    $path = $directory . '/' . $hash . '.json';
    $lockPath = $directory . '/' . $hash . '.lock';

    $oldUmask = umask(0077);

    try {
        $lock = fopen($lockPath, 'c');
    } finally {
        umask($oldUmask);
    }

    if ($lock === false) {
        throw new RuntimeException(
            'No se pudo abrir el bloqueo del lead'
        );
    }

    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException(
                'No se pudo bloquear el lead'
            );
        }

        // Comprobar si el lead ya existe.
        if (file_exists($path)) {
            $raw = file_get_contents($path);

            if ($raw === false || trim($raw) === '') {
                throw new RuntimeException(
                    'Lead existente vacío o ilegible'
                );
            }

            $previous = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (
                !is_array($previous) ||
                ($previous['id'] ?? null) !== $hash ||
                !is_string($previous['estado'] ?? null) ||
                $previous['estado'] === '' ||
                ($previous['confirmado'] ?? null) !== true ||
                ($previous['consentimiento'] ?? null) !== true
            ) {
                throw new RuntimeException(
                    'Lead existente inconsistente'
                );
            }

            // No reutilizar un lead con datos diferentes.
            foreach ($campos as $campo => $valor) {
                if (
                    !is_string($previous[$campo] ?? null) ||
                    $previous[$campo] !== $valor
                ) {
                    throw new RuntimeException(
                        'Ya existe un lead con otros datos'
                    );
                }
            }

            // La confirmación repetida recupera
            // exactamente el registro anterior.
            return $previous;
        }

        $lead = array_merge(
            [
                'id' => $hash
            ],
            $campos,
            [
                'estado' => 'pendiente',
                'canal' => 'whatsapp',
                'confirmado' => true,
                'consentimiento' => true,
                'creado_en' => gmdate('c')
            ]
        );

        $json = json_encode(
            $lead,
            JSON_UNESCAPED_UNICODE |
            JSON_PRETTY_PRINT |
            JSON_THROW_ON_ERROR
        );

        $temp = tempnam(
            $directory,
            '.lead-'
        );

        if ($temp === false) {
            throw new RuntimeException(
                'No se pudo crear el archivo temporal'
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
                    'No se pudo abrir el archivo temporal'
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
                            'Escritura incompleta del lead'
                        );
                    }

                    $written += $n;
                }

                if (!fflush($handle)) {
                    throw new RuntimeException(
                        'No se pudo vaciar el búfer'
                    );
                }

                if (
                    function_exists('fsync') &&
                    !fsync($handle)
                ) {
                    throw new RuntimeException(
                        'No se pudo sincronizar el lead'
                    );
                }

            } finally {
                fclose($handle);
            }

            if (file_get_contents($temp) !== $json) {
                throw new RuntimeException(
                    'Verificación del lead fallida'
                );
            }

            if (!rename($temp, $path)) {
                throw new RuntimeException(
                    'No se pudo publicar el lead'
                );
            }

        } finally {
            if (is_file($temp)) {
                unlink($temp);
            }
        }

        return $lead;

    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

