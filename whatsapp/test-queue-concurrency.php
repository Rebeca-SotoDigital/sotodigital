<?php
declare(strict_types=1);

require_once __DIR__ . '/queue-worker.php';

$base = sys_get_temp_dir()
    . '/soto-concurrency-'
    . bin2hex(random_bytes(6));

if (!mkdir($base, 0700, true)) {
    throw new RuntimeException(
        'No se pudo crear el entorno de pruebas'
    );
}

putenv('SOTO_QUEUE_DIR=' . $base . '/queue');
putenv('SOTO_EVENTS_DIR=' . $base . '/events');
putenv('SOTO_JOURNAL_DIR=' . $base . '/journal');
putenv('SOTO_CONVERSATIONS_DIR=' . $base . '/conversations');
putenv('SOTO_PIPELINE_DIR=' . $base . '/pipeline');
putenv('SOTO_LEADS_DIR=' . $base . '/leads');
putenv('SOTO_WHATSAPP_LIVE_SEND=0');
putenv('SOTO_TEST_SIGNAL=' . $base . '/worker-started');

function verificarConcurrencia(
    bool $ok,
    string $descripcion
): void {
    if (!$ok) {
        throw new RuntimeException(
            'PRUEBA FALLIDA: ' . $descripcion
        );
    }

    echo "[OK] {$descripcion}\n";
}

// Programa auxiliar que ejecutaremos
// como un proceso PHP independiente.
$codigo = '<?php' . "\n" .
    'require_once ' .
    var_export(__DIR__ . '/queue-worker.php', true) .
    ";\n" .
    <<<'PHP'

$modo = $argv[1] ?? '';

if ($modo === 'encolar') {
    echo sotoEncolarMensajeSeguro(
        '34999998888',
        'wamid.CONCURRENCIA_001',
        'Quiero una página web',
        '1800000001'
    );
    exit(0);
}

if ($modo === 'worker') {
    $generador = function (
        string $telefono,
        string $texto
    ): array {
        file_put_contents(
            getenv('SOTO_TEST_SIGNAL'),
            'trabajando'
        );

        // Mantener ocupado el primer worker.
        sleep(2);

        return [
            'respuesta' => 'Respuesta simulada',
            'requiere_marcar_envio' => false
        ];
    };

    $transportador = function (
        string $telefono,
        string $texto
    ): array {
        return [
            'http_status' => 200,
            'body' => json_encode([
                'messages' => [
                    ['id' => 'wamid.CONCURRENCIA_RESPUESTA']
                ]
            ], JSON_THROW_ON_ERROR)
        ];
    };

    try {
        $resultado = procesarColaWhatsApp(
            $transportador,
            10,
            $generador
        );

        echo 'DONE:' . $resultado['completados'];
    } catch (RuntimeException $e) {
        if (str_contains(
            $e->getMessage(),
            'Ya existe un trabajador'
        )) {
            echo 'BUSY';
            exit(0);
        }

        throw $e;
    }

    exit(0);
}

throw new RuntimeException('Modo no reconocido');
PHP;

$runner = $base . '/runner.php';

if (file_put_contents($runner, $codigo) === false) {
    throw new RuntimeException(
        'No se pudo crear el proceso de prueba'
    );
}

function lanzarProceso(
    string $runner,
    string $modo
): array {
    $pipes = [];

    $proceso = proc_open(
        [PHP_BINARY, $runner, $modo],
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ],
        $pipes
    );

    if (!is_resource($proceso)) {
        throw new RuntimeException(
            'No se pudo iniciar el proceso PHP'
        );
    }

    fclose($pipes[0]);

    return [$proceso, $pipes];
}

function recogerProceso(array $proceso): string
{
    [$handle, $pipes] = $proceso;

    $salida = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    $codigo = proc_close($handle);

    if ($codigo !== 0) {
        throw new RuntimeException(
            'Proceso fallido: ' . $error
        );
    }

    return trim($salida);
}

try {
    echo "\n=== PRUEBAS DE CONCURRENCIA ===\n\n";

    // 1. Lanzar cinco procesos que intentan
    // encolar exactamente el mismo mensaje.
    $procesos = [];

    for ($i = 0; $i < 5; $i++) {
        $procesos[] = lanzarProceso(
            $runner,
            'encolar'
        );
    }

    $resultados = [];

    foreach ($procesos as $proceso) {
        $resultados[] = recogerProceso($proceso);
    }

    verificarConcurrencia(
        count(array_filter(
            $resultados,
            fn($r) => $r === 'encolado'
        )) === 1,
        'Solo un proceso crea el mensaje'
    );

    verificarConcurrencia(
        count(array_filter(
            $resultados,
            fn($r) => $r === 'duplicado'
        )) === 4,
        'Los otros cuatro detectan el duplicado'
    );

    $archivos = glob(
        sotoQueueDirectory() . '/*.json'
    );

    verificarConcurrencia(
        count($archivos) === 1,
        'Existe un único registro de mensaje'
    );

    // 2. Arrancar el primer trabajador.
    $worker1 = lanzarProceso(
        $runner,
        'worker'
    );

    // Esperar a que haya adquirido el bloqueo
    // y esté ejecutando el generador simulado.
    $signal = getenv('SOTO_TEST_SIGNAL');
    $detectado = false;

    for ($i = 0; $i < 100; $i++) {
        if (is_file($signal)) {
            $detectado = true;
            break;
        }

        usleep(50000);
    }

    verificarConcurrencia(
        $detectado,
        'Primer trabajador iniciado'
    );

    // 3. Intentar arrancar otro trabajador
    // mientras el primero sigue ocupado.
    $worker2 = lanzarProceso(
        $runner,
        'worker'
    );

    $salida2 = recogerProceso($worker2);
    $salida1 = recogerProceso($worker1);

    verificarConcurrencia(
        $salida1 === 'DONE:1',
        'Primer trabajador completa el mensaje'
    );

    verificarConcurrencia(
        $salida2 === 'BUSY',
        'Segundo trabajador no entra simultáneamente'
    );

    // 4. Comprobar los registros.
    $registro = json_decode(
        file_get_contents($archivos[0]),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    verificarConcurrencia(
        $registro['estado'] === 'completado',
        'El mensaje termina completado'
    );

    $journal = sotoJournalDir() . '/' .
        hash('sha256', 'wamid.CONCURRENCIA_001') .
        '.json';

    $diario = json_decode(
        file_get_contents($journal),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    verificarConcurrencia(
        $diario['estado'] === 'aceptado',
        'El diario registra una única aceptación'
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
