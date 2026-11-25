<?php
declare(strict_types=1);

require_once __DIR__ . '/queue-worker.php';
require_once __DIR__ . '/queue-recovery.php';

$base = sys_get_temp_dir()
    . '/soto-crash-'
    . bin2hex(random_bytes(6));

if (!mkdir($base, 0700, true)) {
    throw new RuntimeException(
        'No se pudo crear el entorno de prueba'
    );
}

putenv('SOTO_QUEUE_DIR=' . $base . '/queue');
putenv('SOTO_EVENTS_DIR=' . $base . '/events');
putenv('SOTO_JOURNAL_DIR=' . $base . '/journal');
putenv('SOTO_CONVERSATIONS_DIR=' . $base . '/conversations');
putenv('SOTO_PIPELINE_DIR=' . $base . '/pipeline');
putenv('SOTO_LEADS_DIR=' . $base . '/leads');
putenv('SOTO_WHATSAPP_LIVE_SEND=0');

$signal = $base . '/envio-iniciado';
putenv('SOTO_CRASH_SIGNAL=' . $signal);

function comprobarCrash(
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

$id = 'wamid.CRASH_PRUEBA_001';
$telefono = '34999998888';

$runnerCode = '<?php' . "\n"
    . 'require_once '
    . var_export(__DIR__ . '/queue-worker.php', true)
    . ";\n"
    . <<<'PHP'

$generador = function (
    string $telefono,
    string $texto
): array {
    return [
        'respuesta' => 'Respuesta simulada de Soto Digital',
        'requiere_marcar_envio' => false
    ];
};

$transportador = function (
    string $telefono,
    string $texto
): array {
    // Avisar de que el envío ya se ha iniciado.
    file_put_contents(
        getenv('SOTO_CRASH_SIGNAL'),
        'iniciado'
    );

    // Simular una comunicación lenta con Meta.
    // El proceso será terminado desde el test.
    sleep(30);

    return [
        'http_status' => 200,
        'body' => json_encode([
            'messages' => [
                ['id' => 'wamid.NO_DEBE_COMPLETARSE']
            ]
        ])
    ];
};

procesarColaWhatsApp(
    $transportador,
    10,
    $generador
);
PHP;

$runner = $base . '/crash-runner.php';

if (file_put_contents($runner, $runnerCode) === false) {
    throw new RuntimeException(
        'No se pudo crear el simulador'
    );
}

$process = null;
$pipes = [];

try {
    echo "\n=== PRUEBA DE INTERRUPCIÓN PHP ===\n\n";

    sotoEncolarMensajeSeguro(
        $telefono,
        $id,
        'Quiero contratar una página web',
        '1800000001'
    );

    comprobarCrash(
        true,
        'Mensaje inicial almacenado'
    );

    if (!function_exists('proc_open')) {
        throw new RuntimeException(
            'proc_open no está disponible'
        );
    }

    $process = proc_open(
        [PHP_BINARY, $runner],
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ],
        $pipes
    );

    if (!is_resource($process)) {
        throw new RuntimeException(
            'No se pudo iniciar el trabajador'
        );
    }

    fclose($pipes[0]);

    $started = false;

    // Esperar como máximo 10 segundos
    // a que comience el envío simulado.
    for ($i = 0; $i < 200; $i++) {
        if (is_file($signal)) {
            $started = true;
            break;
        }

        usleep(50000);
    }

    comprobarCrash(
        $started,
        'Trabajador llega al intento de envío'
    );

    // Terminar abruptamente el proceso PHP.
    if (!proc_terminate($process, 9)) {
        throw new RuntimeException(
            'No se pudo interrumpir el trabajador'
        );
    }

    stream_get_contents($pipes[1]);
    stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    proc_close($process);
    $process = null;

    comprobarCrash(
        true,
        'Proceso PHP interrumpido'
    );

    $queueFile = sotoQueueDirectory() . '/'
        . hash('sha256', $id) . '.json';

    $journalFile = sotoJournalDir() . '/'
        . hash('sha256', $id) . '.json';

    $queue = json_decode(
        file_get_contents($queueFile),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $journal = json_decode(
        file_get_contents($journalFile),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    comprobarCrash(
        $queue['estado'] === 'procesando',
        'La cola conserva el estado interrumpido'
    );

    comprobarCrash(
        $journal['estado'] === 'enviando',
        'El diario conserva el intento iniciado'
    );

    // Volver a ejecutar el trabajador.
    $nuevosEnvios = 0;

    $metaSimulado = function (
        string $telefono,
        string $texto
    ) use (&$nuevosEnvios): array {
        $nuevosEnvios++;

        throw new RuntimeException(
            'No se debería reenviar'
        );
    };

    $generadorSimulado = function (
        string $telefono,
        string $texto
    ): array {
        throw new RuntimeException(
            'No se debería regenerar la respuesta'
        );
    };

    $resultado = procesarColaWhatsApp(
        $metaSimulado,
        10,
        $generadorSimulado
    );

    comprobarCrash(
        $resultado['completados'] === 0,
        'La nueva ejecución no reprocesa el evento'
    );

    comprobarCrash(
        $nuevosEnvios === 0,
        'No se realiza un segundo envío'
    );

    // Envejecer el archivo para simular
    // una operación interrumpida antigua.
    touch($queueFile, time() - 600);
    clearstatcache(true, $queueFile);

    $diagnostico = diagnosticarCola();

    comprobarCrash(
        $diagnostico['interrumpidos'] === 1,
        'El diagnóstico detecta la interrupción'
    );

    echo "\n=== TODAS LAS PRUEBAS SUPERADAS ===\n";
    echo "WhatsApps reales enviados: 0\n";
    echo "Llamadas a Claude: 0\n";

} catch (Throwable $e) {
    if (is_resource($process)) {
        proc_terminate($process, 9);

        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }

        proc_close($process);
    }

    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );

    exit(1);
}
