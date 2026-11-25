<?php
declare(strict_types=1);

require __DIR__ . '/delivery-journal.php';

putenv(
    'SOTO_JOURNAL_DIR=' .
    sys_get_temp_dir() . '/soto-journal-test-' .
    bin2hex(random_bytes(6))
);

function comprobarJournal(
    bool $ok,
    string $descripcion
): void {
    if (!$ok) {
        throw new RuntimeException(
            'Prueba fallida: ' . $descripcion
        );
    }

    echo "[OK] $descripcion\n";
}

function debeFallar(
    callable $operacion,
    string $descripcion
): void {
    $fallo = false;

    try {
        $operacion();
    } catch (RuntimeException $e) {
        $fallo = true;
    }

    comprobarJournal($fallo, $descripcion);
}

try {
    $id = 'wamid.SOTO_JOURNAL_001';

    $r = sotoPrepararEnvio(
        $id,
        'Respuesta ficticia de Soto Digital'
    );

    comprobarJournal(
        $r['estado'] === 'preparado',
        'Respuesta preparada'
    );

    $r = sotoPrepararEnvio(
        $id,
        'Respuesta ficticia de Soto Digital'
    );

    comprobarJournal(
        $r['estado'] === 'preparado',
        'Preparación repetida no duplica'
    );

    $r = sotoIniciarEnvio($id);

    comprobarJournal(
        $r['estado'] === 'enviando',
        'Intento iniciado'
    );

    debeFallar(
        fn() => sotoIniciarEnvio($id),
        'No permite iniciar dos veces'
    );

    $r = sotoCerrarEnvio(
        $id,
        'aceptado',
        'wamid.META_RESPUESTA_001'
    );

    comprobarJournal(
        $r['estado'] === 'aceptado',
        'Meta ha aceptado el envío'
    );

    comprobarJournal(
        $r['meta_message_id'] ===
            'wamid.META_RESPUESTA_001',
        'Identificador de Meta conservado'
    );

    debeFallar(
        fn() => sotoIniciarEnvio($id),
        'No reenvía un mensaje aceptado'
    );

    $id2 = 'wamid.SOTO_JOURNAL_002';

    sotoPrepararEnvio($id2, 'Segunda respuesta');
    sotoIniciarEnvio($id2);

    $r = sotoCerrarEnvio($id2, 'incierto');

    comprobarJournal(
        $r['estado'] === 'incierto',
        'Se registra resultado incierto'
    );

    debeFallar(
        fn() => sotoIniciarEnvio($id2),
        'No reintenta un resultado incierto'
    );

    $id3 = 'wamid.SOTO_JOURNAL_003';

    sotoPrepararEnvio($id3, 'Tercera respuesta');
    sotoIniciarEnvio($id3);

    $r = sotoCerrarEnvio($id3, 'rechazado');

    comprobarJournal(
        $r['estado'] === 'rechazado',
        'Se registra un rechazo'
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
