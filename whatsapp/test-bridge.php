<?php
declare(strict_types=1);

require __DIR__ . '/whatsapp-bridge.php';

$suffix = bin2hex(random_bytes(6));
$base = sys_get_temp_dir() . '/soto-bridge-' . $suffix;

putenv('SOTO_PIPELINE_DIR=' . $base . '/pipeline');
putenv('SOTO_LEADS_DIR=' . $base . '/leads');

$telefono = '34999998888';
$resumen = 'Resumen de propuesta de prueba';

// Simula una respuesta correcta de Meta.
$metaCorrecto = function (
    string $to,
    string $texto
): array {
    return [
        'http_status' => 200,
        'body' => json_encode([
            'messaging_product' => 'whatsapp',
            'messages' => [
                ['id' => 'wamid.SIMULADO123']
            ]
        ])
    ];
};

// Simula un error de permisos de Meta.
$metaError = function (
    string $to,
    string $texto
): array {
    return [
        'http_status' => 403,
        'body' => json_encode([
            'error' => [
                'message' => 'Permiso denegado'
            ]
        ])
    ];
};

function comprobarBridge(
    string $nombre,
    string $esperado,
    array $resultado
): void {
    echo $nombre . ': ' . $resultado['estado'] . "\n";

    if ($resultado['estado'] !== $esperado) {
        throw new RuntimeException(
            'Resultado inesperado en ' . $nombre
        );
    }
}

try {
    // 1. Respuesta normal de Claude.
    $r = ejecutarWhatsAppBridge(
        $telefono,
        [
            'respuesta' => 'Hola, soy Soto Digital.',
            'requiere_marcar_envio' => false
        ],
        $metaCorrecto
    );

    comprobarBridge('1. Mensaje normal', 'aceptado', $r);

    // 2. Meta rechaza el mensaje.
    $r = ejecutarWhatsAppBridge(
        $telefono,
        [
            'respuesta' => 'Otro mensaje de prueba',
            'requiere_marcar_envio' => false
        ],
        $metaError
    );

    comprobarBridge(
        '2. Error de Meta',
        'envio_fallido',
        $r
    );

    // 3. Crear un estado pendiente simulado.
    pipelineEstado(
        $telefono,
        function (array $state) use ($resumen): array {
            return [
                'guardar' => true,
                'estado' => [
                    'fase' => 'pendiente_confirmacion',
                    'creado_en' => time(),
                    'resumen_hash' => hash(
                        'sha256',
                        $resumen
                    ),
                    'envio_aceptado' => false,
                    'datos' => []
                ],
                'resultado' => null
            ];
        }
    );

    // Meta acepta exactamente el resumen pendiente.
    $r = ejecutarWhatsAppBridge(
        $telefono,
        [
            'respuesta' => $resumen,
            'requiere_marcar_envio' => true
        ],
        $metaCorrecto
    );

    comprobarBridge(
        '3. Resumen correcto',
        'aceptado',
        $r
    );

    if ($r['resumen_marcado'] !== true) {
        throw new RuntimeException(
            'No se marcó el resumen'
        );
    }

    // 4. El texto ya no coincide con el borrador.
    $r = ejecutarWhatsAppBridge(
        $telefono,
        [
            'respuesta' => 'Resumen diferente',
            'requiere_marcar_envio' => true
        ],
        $metaCorrecto
    );

    comprobarBridge(
        '4. Resumen distinto',
        'aceptado_sin_vincular',
        $r
    );

    echo "\n=== TODAS LAS PRUEBAS SUPERADAS ===\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );
    exit(1);
}
