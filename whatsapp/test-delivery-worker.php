<?php
declare(strict_types=1);

/**
 * Pruebas del coordinador de envíos.
 *
 * WhatsApp: simulado.
 * Claude: no utilizado.
 */

require __DIR__ . '/delivery-worker.php';

$suffix = bin2hex(random_bytes(6));
$base = sys_get_temp_dir() .
    '/soto-delivery-worker-' . $suffix;

putenv('SOTO_JOURNAL_DIR=' . $base . '/journal');
putenv('SOTO_PIPELINE_DIR=' . $base . '/pipeline');
putenv('SOTO_LEADS_DIR=' . $base . '/leads');
putenv('SOTO_WHATSAPP_LIVE_SEND=0');

$telefono = '34999998888';
$envios = 0;

$metaCorrecto = function (
    string $to,
    string $texto
) use (&$envios): array {
    $envios++;

    return [
        'http_status' => 200,
        'body' => json_encode([
            'messaging_product' => 'whatsapp',
            'messages' => [
                ['id' => 'wamid.META_SIMULADO_001']
            ]
        ], JSON_THROW_ON_ERROR)
    ];
};

$metaRechazo = function (
    string $to,
    string $texto
) use (&$envios): array {
    $envios++;

    return [
        'http_status' => 403,
        'body' => json_encode([
            'error' => [
                'code' => 10,
                'message' => 'Permiso denegado'
            ]
        ], JSON_THROW_ON_ERROR)
    ];
};

$metaSinConexion = function (
    string $to,
    string $texto
) use (&$envios): array {
    $envios++;

    throw new RuntimeException(
        'Se perdió la conexión con Meta'
    );
};

function verificarEntrega(
    bool $condicion,
    string $descripcion
): void {
    if (!$condicion) {
        throw new RuntimeException(
            'PRUEBA FALLIDA: ' . $descripcion
        );
    }

    echo "[OK] $descripcion\n";
}

try {
    echo "\n=== PRUEBAS DELIVERY WORKER ===\n\n";

    // 1. Envío correcto.
    $id1 = 'wamid.ENTRANTE_001';

    $r = procesarEntregaConDiario(
        $id1,
        $telefono,
        [
            'respuesta' => 'Hola desde Soto Digital',
            'requiere_marcar_envio' => false
        ],
        $metaCorrecto
    );

    verificarEntrega(
        $r['estado'] === 'aceptado',
        'Meta acepta la respuesta'
    );

    $journal1 = sotoJournalUpdate(
        $id1,
        fn(array $state) => $state
    );

    verificarEntrega(
        $journal1['estado'] === 'aceptado',
        'El diario registra aceptación'
    );

    verificarEntrega(
        isset($journal1['meta_message_id']),
        'Se conserva el ID de Meta'
    );

    // 2. Repetición: no debe volver a enviar.
    $antes = $envios;
    $bloqueado = false;

    try {
        procesarEntregaConDiario(
            $id1,
            $telefono,
            [
                'respuesta' => 'Hola desde Soto Digital',
                'requiere_marcar_envio' => false
            ],
            $metaCorrecto
        );
    } catch (RuntimeException $e) {
        $bloqueado = true;
    }

    verificarEntrega(
        $bloqueado,
        'Segundo intento bloqueado'
    );

    verificarEntrega(
        $envios === $antes,
        'No se duplicó el envío'
    );

    // 3. Rechazo explícito de Meta.
    $id2 = 'wamid.ENTRANTE_002';

    $r = procesarEntregaConDiario(
        $id2,
        $telefono,
        [
            'respuesta' => 'Mensaje rechazado',
            'requiere_marcar_envio' => false
        ],
        $metaRechazo
    );

    verificarEntrega(
        $r['estado'] === 'rechazado',
        'Rechazo HTTP 403 identificado'
    );

    $journal2 = sotoJournalUpdate(
        $id2,
        fn(array $state) => $state
    );

    verificarEntrega(
        $journal2['estado'] === 'rechazado',
        'El rechazo queda registrado'
    );

    // 4. Conexión incierta.
    $id3 = 'wamid.ENTRANTE_003';

    $r = procesarEntregaConDiario(
        $id3,
        $telefono,
        [
            'respuesta' => 'Mensaje sin confirmación',
            'requiere_marcar_envio' => false
        ],
        $metaSinConexion
    );

    verificarEntrega(
        $r['estado'] === 'incierto',
        'Pérdida de conexión detectada'
    );

    $journal3 = sotoJournalUpdate(
        $id3,
        fn(array $state) => $state
    );

    verificarEntrega(
        $journal3['estado'] === 'incierto',
        'Resultado incierto registrado'
    );

    // 5. Resumen comercial sin vinculación.
    $id4 = 'wamid.ENTRANTE_004';

    $r = procesarEntregaConDiario(
        $id4,
        $telefono,
        [
            'respuesta' => 'Resumen sin borrador asociado',
            'requiere_marcar_envio' => true
        ],
        $metaCorrecto
    );

    verificarEntrega(
        $r['estado'] === 'revision_vinculacion',
        'Detecta resumen no vinculado'
    );

    verificarEntrega(
        $r['aceptado'] === true,
        'No confunde el fallo de vinculación con un rechazo'
    );

    $journal4 = sotoJournalUpdate(
        $id4,
        fn(array $state) => $state
    );

    verificarEntrega(
        $journal4['estado'] === 'aceptado',
        'Conserva la aceptación de Meta'
    );

    echo "\n=== TODAS LAS PRUEBAS SUPERADAS ===\n";
    echo "WhatsApps reales enviados: 0\n";
    echo "Llamadas a Claude: 0\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        "ERROR: " . $e->getMessage() . "\n"
    );
    exit(1);
}
