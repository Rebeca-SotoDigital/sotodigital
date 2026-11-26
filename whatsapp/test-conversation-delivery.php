<?php
declare(strict_types=1);

require_once __DIR__ . '/queue-worker.php';

$base = sys_get_temp_dir()
    . '/soto-memory-delivery-'
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
$clienteC = '34999996666';

$generaciones = 0;
$envios = 0;

$claudeSimulado = function (
    string $telefono,
    string $texto
) use (&$generaciones): array {
    $generaciones++;

    return [
        'estado' => 'respuesta_claude',
        'respuesta' =>
            'Respuesta comercial a: ' . $texto,
        'requiere_marcar_envio' => false
    ];
};

$metaSimulado = function (
    string $telefono,
    string $texto
) use (&$envios, $clienteB, $clienteC): array {
    $envios++;

    if ($telefono === $clienteB) {
        return [
            'http_status' => 403,
            'body' => json_encode([
                'error' => [
                    'code' => 10,
                    'message' => 'Rechazo simulado'
                ]
            ], JSON_THROW_ON_ERROR)
        ];
    }

    if ($telefono === $clienteC) {
        throw new RuntimeException(
            'Conexión interrumpida'
        );
    }

    return [
        'http_status' => 200,
        'body' => json_encode([
            'messaging_product' => 'whatsapp',
            'messages' => [
                ['id' => 'wamid.MEMORY_RESPONSE_001']
            ]
        ], JSON_THROW_ON_ERROR)
    ];
};

function comprobarMemoriaEntrega(
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

function estadoColaMemoria(string $id): string
{
    $archivo = sotoQueueDirectory() . '/' .
        hash('sha256', $id) . '.json';

    $registro = json_decode(
        file_get_contents($archivo),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    return $registro['estado'];
}

function estadoDiarioMemoria(string $id): string
{
    $archivo = sotoJournalDir() . '/' .
        hash('sha256', $id) . '.json';

    $registro = json_decode(
        file_get_contents($archivo),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    return $registro['estado'];
}

try {
    echo "\n=== PRUEBAS MEMORIA + ENTREGA ===\n\n";

    // CASO 1: Meta acepta.
    $id1 = 'wamid.MEMORY_DELIVERY_001';

    sotoEncolarMensajeSeguro(
        $clienteA,
        $id1,
        'Necesito una página web',
        '1800000001'
    );

    $r = procesarColaWhatsApp(
        $metaSimulado,
        10,
        $claudeSimulado
    );

    comprobarMemoriaEntrega(
        $r['completados'] === 1,
        'Mensaje aceptado y completado'
    );

    $historialA = cargarConversacion($clienteA);

    comprobarMemoriaEntrega(
        count($historialA) === 2,
        'Se consolida el intercambio completo'
    );

    comprobarMemoriaEntrega(
        $historialA[0]['role'] === 'user' &&
        $historialA[1]['role'] === 'assistant',
        'Los turnos mantienen el orden'
    );

    comprobarMemoriaEntrega(
        estadoDiarioMemoria($id1) === 'aceptado',
        'El diario confirma la aceptación'
    );

    // CASO 2: Meta rechaza.
    $id2 = 'wamid.MEMORY_DELIVERY_002';

    sotoEncolarMensajeSeguro(
        $clienteB,
        $id2,
        'Necesito gestionar Instagram',
        '1800000002'
    );

    $r = procesarColaWhatsApp(
        $metaSimulado,
        10,
        $claudeSimulado
    );

    comprobarMemoriaEntrega(
        $r['revision'] === 1,
        'Un rechazo queda en revisión'
    );

    comprobarMemoriaEntrega(
        cargarConversacion($clienteB) === [],
        'La respuesta rechazada no entra en memoria'
    );

    comprobarMemoriaEntrega(
        estadoDiarioMemoria($id2) === 'rechazado',
        'El rechazo queda documentado'
    );

    // CASO 3: resultado incierto.
    $id3 = 'wamid.MEMORY_DELIVERY_003';

    sotoEncolarMensajeSeguro(
        $clienteC,
        $id3,
        'Quiero automatizar mis reservas',
        '1800000003'
    );

    $r = procesarColaWhatsApp(
        $metaSimulado,
        10,
        $claudeSimulado
    );

    comprobarMemoriaEntrega(
        $r['revision'] === 1,
        'La conexión incierta queda en revisión'
    );

    comprobarMemoriaEntrega(
        cargarConversacion($clienteC) === [],
        'El resultado incierto no entra en memoria'
    );

    comprobarMemoriaEntrega(
        estadoDiarioMemoria($id3) === 'incierto',
        'El diario conserva la incertidumbre'
    );

    // CASO 4: volver a ejecutar la cola.
    $generacionesPrevias = $generaciones;
    $enviosPrevios = $envios;

    procesarColaWhatsApp(
        $metaSimulado,
        10,
        $claudeSimulado
    );

    comprobarMemoriaEntrega(
        $generaciones === $generacionesPrevias,
        'No se vuelven a generar respuestas'
    );

    comprobarMemoriaEntrega(
        $envios === $enviosPrevios,
        'No se producen nuevos envíos'
    );

    comprobarMemoriaEntrega(
        count(cargarConversacion($clienteA)) === 2,
        'El intercambio aceptado no se duplica'
    );

    comprobarMemoriaEntrega(
        estadoColaMemoria($id1) === 'completado' &&
        estadoColaMemoria($id2) === 'revision' &&
        estadoColaMemoria($id3) === 'revision',
        'Los tres estados finales son coherentes'
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
