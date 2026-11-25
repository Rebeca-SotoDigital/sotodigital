<?php
declare(strict_types=1);

require __DIR__ . '/queue-worker.php';

// Cada ejecución utiliza almacenamiento aislado.
$base = sys_get_temp_dir()
    . '/soto-queue-delivery-'
    . bin2hex(random_bytes(6));

putenv('SOTO_QUEUE_DIR=' . $base . '/queue');
putenv('SOTO_EVENTS_DIR=' . $base . '/events');
putenv('SOTO_JOURNAL_DIR=' . $base . '/journal');
putenv('SOTO_CONVERSATIONS_DIR=' . $base . '/conversations');
putenv('SOTO_PIPELINE_DIR=' . $base . '/pipeline');
putenv('SOTO_LEADS_DIR=' . $base . '/leads');
putenv('SOTO_WHATSAPP_LIVE_SEND=0');

$telefono = '34999998888';
$generaciones = 0;
$envios = 0;

// Simula Claude sin consumir créditos.
$claudeSimulado = function (
    string $telefono,
    string $texto
) use (&$generaciones): array {
    $generaciones++;

    return [
        'respuesta' =>
            'Hola, somos Soto Digital. ' .
            'Cuéntanos qué necesita tu negocio.',
        'requiere_marcar_envio' => false
    ];
};

// Simula una aceptación de Meta.
$metaCorrecto = function (
    string $telefono,
    string $texto
) use (&$envios): array {
    $envios++;

    return [
        'http_status' => 200,
        'body' => json_encode([
            'messaging_product' => 'whatsapp',
            'messages' => [
                [
                    'id' => 'wamid.RESPUESTA_SIMULADA'
                ]
            ]
        ], JSON_THROW_ON_ERROR)
    ];
};

$metaRechazado = function (
    string $telefono,
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

$metaIncierto = function (
    string $telefono,
    string $texto
) use (&$envios): array {
    $envios++;

    throw new RuntimeException(
        'Fallo de conexión simulado'
    );
};

function comprobarIntegracion(
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

function leerDiarioPrueba(string $id): array
{
    $path = sotoJournalDir() . '/' .
        hash('sha256', $id) . '.json';

    return json_decode(
        file_get_contents($path),
        true,
        512,
        JSON_THROW_ON_ERROR
    );
}

try {
    echo "\n=== INTEGRACIÓN COLA + DIARIO ===\n\n";

    // PRUEBA 1: envío aceptado.
    $id1 = 'wamid.QUEUE_DELIVERY_001';

    sotoEncolarMensaje(
        $telefono,
        $id1,
        'Quiero conseguir más clientes'
    );

    $r = procesarColaWhatsApp(
        $metaCorrecto,
        10,
        $claudeSimulado
    );

    comprobarIntegracion(
        $r['completados'] === 1,
        'La cola completa el primer mensaje'
    );

    comprobarIntegracion(
        leerDiarioPrueba($id1)['estado'] ===
            'aceptado',
        'El diario registra la aceptación'
    );

    comprobarIntegracion(
        $generaciones === 1 && $envios === 1,
        'Una respuesta generada y un envío'
    );

    // PRUEBA 2: no repetir un envío completado.
    $r = procesarColaWhatsApp(
        $metaCorrecto,
        10,
        $claudeSimulado
    );

    comprobarIntegracion(
        $r['completados'] === 0 &&
        $envios === 1 &&
        $generaciones === 1,
        'La segunda ejecución no duplica'
    );

    // PRUEBA 3: Meta rechaza un mensaje.
    $id2 = 'wamid.QUEUE_DELIVERY_002';

    sotoEncolarMensaje(
        $telefono,
        $id2,
        'Necesito una página web'
    );

    $r = procesarColaWhatsApp(
        $metaRechazado,
        10,
        $claudeSimulado
    );

    comprobarIntegracion(
        $r['revision'] === 1,
        'Un rechazo deja la cola en revisión'
    );

    comprobarIntegracion(
        leerDiarioPrueba($id2)['estado'] ===
            'rechazado',
        'El diario conserva el rechazo'
    );

    // PRUEBA 4: conexión incierta.
    $id3 = 'wamid.QUEUE_DELIVERY_003';

    sotoEncolarMensaje(
        $telefono,
        $id3,
        'Quiero gestionar mis redes sociales'
    );

    $r = procesarColaWhatsApp(
        $metaIncierto,
        10,
        $claudeSimulado
    );

    comprobarIntegracion(
        $r['revision'] === 1,
        'La conexión incierta requiere revisión'
    );

    comprobarIntegracion(
        leerDiarioPrueba($id3)['estado'] ===
            'incierto',
        'El diario conserva el resultado incierto'
    );

    // PRUEBA 5: no recuperar a ciegas.
    $enviosPrevios = $envios;
    $generacionesPrevias = $generaciones;

    $r = procesarColaWhatsApp(
        $metaCorrecto,
        10,
        $claudeSimulado
    );

    comprobarIntegracion(
        $r['completados'] === 0,
        'No se reintentan eventos en revisión'
    );

    comprobarIntegracion(
        $envios === $enviosPrevios,
        'No se hacen nuevos envíos'
    );

    comprobarIntegracion(
        $generaciones === $generacionesPrevias,
        'No se generan respuestas duplicadas'
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
