<?php
declare(strict_types=1);

require __DIR__ . '/queue-storage.php';

$base = sys_get_temp_dir()
    . '/soto-storage-test-'
    . bin2hex(random_bytes(6));

putenv('SOTO_QUEUE_DIR=' . $base . '/queue');

function comprobarStorage(
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
    echo "\n=== PRUEBAS QUEUE STORAGE ===\n\n";

    $telefono = '34999998888';
    $id1 = 'wamid.STORAGE_PRUEBA_001';

    // 1. Primer mensaje.
    $r = sotoEncolarMensajeSeguro(
        $telefono,
        $id1,
        'Hola, necesito una página web'
    );

    comprobarStorage(
        $r === 'encolado',
        'Primer mensaje almacenado'
    );

    $archivo = sotoQueueDirectory() . '/' .
        hash('sha256', $id1) . '.json';

    $registro = json_decode(
        file_get_contents($archivo),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    comprobarStorage(
        $registro['estado'] === 'pendiente',
        'El mensaje queda pendiente'
    );

    comprobarStorage(
        $registro['message_id'] === $id1,
        'Se conserva el ID de Meta'
    );

    // 2. Intento de duplicado.
    $r = sotoEncolarMensajeSeguro(
        $telefono,
        $id1,
        'Hola, necesito una página web'
    );

    comprobarStorage(
        $r === 'duplicado',
        'El duplicado se bloquea'
    );

    // 3. Otro mensaje.
    $id2 = 'wamid.STORAGE_PRUEBA_002';

    $r = sotoEncolarMensajeSeguro(
        $telefono,
        $id2,
        'También necesito gestionar Instagram'
    );

    comprobarStorage(
        $r === 'encolado',
        'Segundo mensaje independiente almacenado'
    );

    // 4. Solo dos archivos de mensajes publicados.
    $archivos = glob(
        sotoQueueDirectory() . '/*.json'
    );

    comprobarStorage(
        count($archivos) === 2,
        'Solo existen dos mensajes'
    );

    // 5. No quedan temporales de escritura.
    $temporales = glob(
        sotoQueueDirectory() . '/.soto-pending-*'
    );

    comprobarStorage(
        count($temporales) === 0,
        'No quedan temporales tras escribir'
    );

    // 6. Simular archivo corrupto.
    $id3 = 'wamid.STORAGE_CORRUPTO_003';

    $corrupto = sotoQueueDirectory() . '/' .
        hash('sha256', $id3) . '.json';

    file_put_contents(
        $corrupto,
        '{"estado":'
    );

    $errorDetectado = false;

    try {
        sotoEncolarMensajeSeguro(
            $telefono,
            $id3,
            'Mensaje que no debe sobrescribir'
        );
    } catch (RuntimeException $e) {
        $errorDetectado = true;
    }

    comprobarStorage(
        $errorDetectado,
        'El registro corrupto se detecta'
    );

    comprobarStorage(
        file_get_contents($corrupto) ===
            '{"estado":',
        'El registro corrupto no se sobrescribe'
    );

    // 7. Mensajes que el worker debe poder leer.
    $registro2 = json_decode(
        file_get_contents(
            sotoQueueDirectory() . '/' .
            hash('sha256', $id2) . '.json'
        ),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    comprobarStorage(
        ($registro2['estado'] ?? null) ===
            'pendiente' &&
        ($registro2['intentos'] ?? null) === 0,
        'Formato compatible con Queue Worker'
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
