<?php
declare(strict_types=1);

require __DIR__ . '/message-guard.php';

$suffix = bin2hex(random_bytes(6));

putenv(
    'SOTO_EVENTS_DIR=' .
    sys_get_temp_dir() . '/soto-guard-test-' . $suffix
);

$telefono = '34999998888';
$ejecuciones = 0;

$operacion = function () use (&$ejecuciones): array {
    $ejecuciones++;
    return ['ok' => true];
};

function verificarGuard(
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
    echo "\n=== PRUEBAS MESSAGE GUARD ===\n\n";

    // Primera recepción
    $r = procesarMensajeProtegido(
        $telefono,
        'wamid.PRUEBA001',
        $operacion
    );

    verificarGuard(
        $r['estado'] === 'completado',
        'Primer mensaje procesado'
    );

    // Meta reenvía exactamente el mismo evento
    $r = procesarMensajeProtegido(
        $telefono,
        'wamid.PRUEBA001',
        $operacion
    );

    verificarGuard(
        $r['estado'] === 'duplicado',
        'Mensaje duplicado bloqueado'
    );

    verificarGuard(
        $ejecuciones === 1,
        'El código solamente se ejecutó una vez'
    );

    // Otro mensaje del mismo cliente
    $r = procesarMensajeProtegido(
        $telefono,
        'wamid.PRUEBA002',
        $operacion
    );

    verificarGuard(
        $r['estado'] === 'completado',
        'Segundo mensaje independiente procesado'
    );

    verificarGuard(
        $ejecuciones === 2,
        'Dos mensajes únicos, dos ejecuciones'
    );

    // Simulación de un fallo
    $fallos = 0;

    $operacionFallida = function () use (
        &$fallos
    ): array {
        $fallos++;

        throw new RuntimeException(
            'Fallo simulado de Claude o Meta'
        );
    };

    $r = procesarMensajeProtegido(
        $telefono,
        'wamid.PRUEBA003',
        $operacionFallida
    );

    verificarGuard(
        $r['estado'] === 'revision',
        'El fallo queda pendiente de revisión'
    );

    // No repetir un evento de resultado incierto
    $r = procesarMensajeProtegido(
        $telefono,
        'wamid.PRUEBA003',
        $operacionFallida
    );

    verificarGuard(
        $r['estado'] === 'duplicado',
        'No se reintenta automáticamente'
    );

    verificarGuard(
        $fallos === 1,
        'No se ejecutó nuevamente la operación fallida'
    );

    echo "\n=== TODAS LAS PRUEBAS SUPERADAS ===\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );
    exit(1);
}
