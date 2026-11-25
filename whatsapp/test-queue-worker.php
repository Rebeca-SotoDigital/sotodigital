<?php
declare(strict_types=1);

require __DIR__ . '/queue-worker.php';

$suffix = bin2hex(random_bytes(6));
$base = sys_get_temp_dir() . '/soto-worker-' . $suffix;

putenv('SOTO_QUEUE_DIR=' . $base . '/queue');
putenv('SOTO_EVENTS_DIR=' . $base . '/events');
putenv('SOTO_CONVERSATIONS_DIR=' . $base . '/conversations');
putenv('SOTO_PIPELINE_DIR=' . $base . '/pipeline');
putenv('SOTO_LEADS_DIR=' . $base . '/leads');
putenv('SOTO_WHATSAPP_LIVE_SEND=0');

$envios = 0;

$metaSimulado = function (
    string $telefono,
    string $texto
) use (&$envios): array {
    $envios++;

    return [
        'http_status' => 200,
        'body' => json_encode([
            'messaging_product' => 'whatsapp',
            'messages' => [
                ['id' => 'wamid.SIMULACION_WORKER']
            ]
        ], JSON_THROW_ON_ERROR)
    ];
};

function comprobarWorker(
    bool $condicion,
    string $descripcion
): void {
    if (!$condicion) {
        throw new RuntimeException(
            'Prueba fallida: ' . $descripcion
        );
    }

    echo "[OK] $descripcion\n";
}

try {
    echo "=== PRUEBA QUEUE WORKER ===\n\n";

    $estado = sotoEncolarMensaje(
        '34999998888',
        'wamid.WORKER_PRUEBA_001',
        'Hola, tengo una cafetería en Santander ' .
        'y quiero conseguir más clientes.'
    );

    comprobarWorker(
        $estado === 'encolado',
        'Mensaje encolado'
    );

    $resultado = procesarColaWhatsApp(
        $metaSimulado,
        10
    );

    comprobarWorker(
        $resultado['completados'] === 1,
        'Mensaje procesado correctamente'
    );

    comprobarWorker(
        $envios === 1,
        'Meta simulado recibió una respuesta'
    );

    $historial = cargarConversacion(
        '34999998888'
    );

    comprobarWorker(
        count($historial) === 2,
        'Claude generó una conversación con memoria'
    );

    $resultado2 = procesarColaWhatsApp(
        $metaSimulado,
        10
    );

    comprobarWorker(
        $resultado2['completados'] === 0,
        'No se vuelve a procesar el mensaje'
    );

    comprobarWorker(
        $envios === 1,
        'No se duplica la respuesta'
    );

    echo "\n=== TODAS LAS PRUEBAS SUPERADAS ===\n";
    echo "WhatsApps reales enviados: 0\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );
    exit(1);
}
