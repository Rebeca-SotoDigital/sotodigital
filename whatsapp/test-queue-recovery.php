<?php
declare(strict_types=1);

require __DIR__ . '/queue-recovery.php';

$suffix = bin2hex(random_bytes(6));
$dir = sys_get_temp_dir() .
    '/soto-recovery-test-' . $suffix;

putenv('SOTO_QUEUE_DIR=' . $dir);

$phone = '34999998888';

function crearCasoRecovery(
    string $messageId,
    string $estado,
    int $segundosAntiguedad = 0
): void {
    global $phone;

    sotoEncolarMensaje(
        $phone,
        $messageId,
        'Mensaje ficticio para comprobar la cola'
    );

    $archivo = sotoQueueDirectory() . '/' .
        hash('sha256', $messageId) . '.json';

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

    if ($segundosAntiguedad > 0) {
        touch(
            $archivo,
            time() - $segundosAntiguedad
        );
    }

    clearstatcache(true, $archivo);
}

function comprobarRecovery(
    bool $condicion,
    string $descripcion
): void {
    if (!$condicion) {
        throw new RuntimeException(
            'Prueba fallida: ' . $descripcion
        );
    }

    echo "[OK] {$descripcion}\n";
}

try {
    crearCasoRecovery(
        'wamid.RECOVERY001',
        'pendiente'
    );

    crearCasoRecovery(
        'wamid.RECOVERY002',
        'completado'
    );

    crearCasoRecovery(
        'wamid.RECOVERY003',
        'revision'
    );

    crearCasoRecovery(
        'wamid.RECOVERY004',
        'procesando',
        600
    );

    crearCasoRecovery(
        'wamid.RECOVERY005',
        'procesando'
    );

    // Simular un archivo JSON incompleto.
    file_put_contents(
        sotoQueueDirectory() . '/corrupto.json',
        '{"estado":'
    );

    $resultado = diagnosticarCola();

    mostrarDiagnosticoCola($resultado);

    echo "\n=== VERIFICACIONES ===\n";

    comprobarRecovery(
        $resultado['total'] === 6,
        'Detecta seis registros'
    );

    comprobarRecovery(
        $resultado['pendientes'] === 1,
        'Detecta un mensaje pendiente'
    );

    comprobarRecovery(
        $resultado['completados'] === 1,
        'Detecta un mensaje completado'
    );

    comprobarRecovery(
        $resultado['revision'] === 1,
        'Detecta un mensaje en revisión'
    );

    comprobarRecovery(
        $resultado['interrumpidos'] === 1,
        'Detecta una posible interrupción'
    );

    comprobarRecovery(
        $resultado['procesando'] === 1,
        'Distingue un proceso reciente'
    );

    comprobarRecovery(
        $resultado['corruptos'] === 1,
        'Detecta un archivo dañado'
    );

    // El diagnóstico no debe cambiar archivos.
    $segundaLectura = diagnosticarCola();

    comprobarRecovery(
        $segundaLectura['total'] === 6,
        'El diagnóstico no elimina mensajes'
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
