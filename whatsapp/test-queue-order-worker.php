<?php
declare(strict_types=1);

require __DIR__ . '/queue-worker.php';

$base = sys_get_temp_dir()
    . '/soto-order-worker-'
    . bin2hex(random_bytes(6));

putenv('SOTO_QUEUE_DIR=' . $base . '/queue');
putenv('SOTO_EVENTS_DIR=' . $base . '/events');
putenv('SOTO_JOURNAL_DIR=' . $base . '/journal');
putenv('SOTO_CONVERSATIONS_DIR=' . $base . '/conversations');
putenv('SOTO_PIPELINE_DIR=' . $base . '/pipeline');
putenv('SOTO_LEADS_DIR=' . $base . '/leads');
putenv('SOTO_WHATSAPP_LIVE_SEND=0');

$clienteA = '34999998888';
$clienteB = '34999997777';

$generados = [];
$enviados = [];
$fallarPrimero = false;

$claudeSimulado = function (
    string $telefono,
    string $texto
) use (&$generados): array {
    $generados[] = [
        'telefono' => $telefono,
        'texto' => $texto
    ];

    return [
        'respuesta' => 'Respuesta a: ' . $texto,
        'requiere_marcar_envio' => false
    ];
};

$metaSimulado = function (
    string $telefono,
    string $texto
) use (&$enviados, &$fallarPrimero): array {
    $enviados[] = [
        'telefono' => $telefono,
        'texto' => $texto
    ];

    if (
        $fallarPrimero &&
        str_contains($texto, 'Mensaje A2')
    ) {
        return [
            'http_status' => 403,
            'body' => json_encode([
                'error' => [
                    'code' => 10,
                    'message' => 'Rechazo simulado'
                ]
            ])
        ];
    }

    return [
        'http_status' => 200,
        'body' => json_encode([
            'messages' => [
                [
                    'id' => 'wamid.RESPUESTA_' .
                        count($enviados)
                ]
            ]
        ])
    ];
};

function verificarOrdenWorker(
    bool $condicion,
    string $descripcion
): void {
    if (!$condicion) {
        throw new RuntimeException(
            'PRUEBA FALLIDA: ' . $descripcion
        );
    }

    echo "[OK] {$descripcion}\n";
}

try {
    echo "\n=== PRUEBA ORDEN + WORKER ===\n\n";

    // Tres mensajes del cliente A.
    sotoEncolarMensajeSeguro(
        $clienteA,
        'wamid.ORDER_WORKER_A1',
        'Mensaje A1',
        '1800000001'
    );

    sotoEncolarMensajeSeguro(
        $clienteA,
        'wamid.ORDER_WORKER_A2',
        'Mensaje A2',
        '1800000002'
    );

    sotoEncolarMensajeSeguro(
        $clienteA,
        'wamid.ORDER_WORKER_A3',
        'Mensaje A3',
        '1800000003'
    );

    // Un mensaje independiente del cliente B.
    sotoEncolarMensajeSeguro(
        $clienteB,
        'wamid.ORDER_WORKER_B1',
        'Mensaje B1',
        '1800000004'
    );

    // Primera tanda.
    $r = procesarColaWhatsApp(
        $metaSimulado,
        10,
        $claudeSimulado
    );

    verificarOrdenWorker(
        $r['completados'] === 2,
        'Procesa un mensaje de cada cliente'
    );

    verificarOrdenWorker(
        count($generados) === 2,
        'Solo genera dos respuestas'
    );

    $textos = array_column($generados, 'texto');

    verificarOrdenWorker(
        in_array('Mensaje A1', $textos, true) &&
        in_array('Mensaje B1', $textos, true),
        'Procesa A1 y B1 primero'
    );

    verificarOrdenWorker(
        !in_array('Mensaje A2', $textos, true) &&
        !in_array('Mensaje A3', $textos, true),
        'A2 y A3 esperan su turno'
    );

    // Segunda tanda: A2 fallará.
    $fallarPrimero = true;

    $r = procesarColaWhatsApp(
        $metaSimulado,
        10,
        $claudeSimulado
    );

    verificarOrdenWorker(
        $r['revision'] === 1,
        'A2 queda en revisión tras rechazo'
    );

    $textos = array_column($generados, 'texto');

    verificarOrdenWorker(
        in_array('Mensaje A2', $textos, true),
        'Se intentó procesar A2'
    );

    verificarOrdenWorker(
        !in_array('Mensaje A3', $textos, true),
        'A3 no se adelanta'
    );

    // Tercera tanda: A3 debe seguir bloqueado.
    $numeroGeneraciones = count($generados);
    $numeroEnvios = count($enviados);

    $r = procesarColaWhatsApp(
        $metaSimulado,
        10,
        $claudeSimulado
    );

    verificarOrdenWorker(
        $r['completados'] === 0,
        'No hay mensajes nuevos habilitados'
    );

    verificarOrdenWorker(
        count($generados) === $numeroGeneraciones,
        'No genera respuestas posteriores al fallo'
    );

    verificarOrdenWorker(
        count($enviados) === $numeroEnvios,
        'No realiza envíos adicionales'
    );

    $plan = planificarColaWhatsApp();

    verificarOrdenWorker(
        $plan['total_elegibles'] === 0 &&
        $plan['total_bloqueados'] === 2,
        'El planificador conserva el bloqueo'
    );

    echo "\n=== TODAS LAS PRUEBAS SUPERADAS ===\n";
    echo "WhatsApps reales enviados: 0\n";
    echo "Llamadas reales a Claude: 0\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );
    exit(1);
}
