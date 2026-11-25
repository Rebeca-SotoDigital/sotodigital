<?php
declare(strict_types=1);

require __DIR__ . '/queue-order.php';

$base = sys_get_temp_dir()
    . '/soto-order-test-'
    . bin2hex(random_bytes(6));

putenv('SOTO_QUEUE_DIR=' . $base . '/queue');

function verificarOrden(
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

function cambiarEstadoOrden(
    string $id,
    string $estado
): void {
    $archivo = sotoQueueDirectory() . '/' .
        hash('sha256', $id) . '.json';

    $registro = json_decode(
        file_get_contents($archivo),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $registro['estado'] = $estado;

    file_put_contents(
        $archivo,
        json_encode($registro, JSON_THROW_ON_ERROR)
    );
}

function idsElegiblesOrden(array $plan): array
{
    $ids = [];

    foreach ($plan['elegibles'] as $archivo) {
        $registro = json_decode(
            file_get_contents($archivo),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $ids[] = $registro['message_id'];
    }

    return $ids;
}

try {
    echo "\n=== PRUEBAS DE ORDEN DE COLA ===\n\n";

    $clienteA = '34999998888';
    $clienteB = '34999997777';

    // Tres mensajes del mismo cliente.
    sotoEncolarMensajeSeguro(
        $clienteA,
        'wamid.ORDEN001',
        'Tengo una cafetería',
        '1800000001'
    );

    sotoEncolarMensajeSeguro(
        $clienteA,
        'wamid.ORDEN002',
        'Necesito una página web',
        '1800000002'
    );

    sotoEncolarMensajeSeguro(
        $clienteA,
        'wamid.ORDEN003',
        'También quiero gestionar Instagram',
        '1800000003'
    );

    // Otro cliente independiente.
    sotoEncolarMensajeSeguro(
        $clienteB,
        'wamid.ORDEN004',
        'Quiero mejorar mi marca',
        '1800000004'
    );

    $plan = planificarColaWhatsApp();
    $ids = idsElegiblesOrden($plan);

    verificarOrden(
        $plan['total_elegibles'] === 2,
        'Selecciona un mensaje por cliente'
    );

    verificarOrden(
        in_array('wamid.ORDEN001', $ids, true),
        'Selecciona el primer mensaje del cliente A'
    );

    verificarOrden(
        in_array('wamid.ORDEN004', $ids, true),
        'El cliente B puede avanzar'
    );

    verificarOrden(
        $plan['total_bloqueados'] === 2,
        'Bloquea los dos mensajes posteriores'
    );

    // El primer mensaje termina correctamente.
    cambiarEstadoOrden(
        'wamid.ORDEN001',
        'completado'
    );

    $plan = planificarColaWhatsApp();
    $ids = idsElegiblesOrden($plan);

    verificarOrden(
        in_array('wamid.ORDEN002', $ids, true),
        'El segundo mensaje ya puede avanzar'
    );

    verificarOrden(
        !in_array('wamid.ORDEN003', $ids, true),
        'El tercero sigue esperando'
    );

    // El segundo mensaje presenta una incidencia.
    cambiarEstadoOrden(
        'wamid.ORDEN002',
        'revision'
    );

    $plan = planificarColaWhatsApp();
    $ids = idsElegiblesOrden($plan);

    verificarOrden(
        !in_array('wamid.ORDEN003', $ids, true),
        'Una incidencia bloquea mensajes posteriores'
    );

    // El otro cliente sigue siendo independiente.
    verificarOrden(
        in_array('wamid.ORDEN004', $ids, true),
        'Una incidencia no bloquea a otros clientes'
    );

    // Simulamos resolución supervisada.
    cambiarEstadoOrden(
        'wamid.ORDEN002',
        'completado'
    );

    $plan = planificarColaWhatsApp();
    $ids = idsElegiblesOrden($plan);

    verificarOrden(
        in_array('wamid.ORDEN003', $ids, true),
        'El tercer mensaje puede avanzar tras resolver'
    );

    echo "\n=== TODAS LAS PRUEBAS SUPERADAS ===\n";
    echo "WhatsApps reales enviados: 0\n";
    echo "Llamadas a Claude: 0\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );
    exit(1);
}
