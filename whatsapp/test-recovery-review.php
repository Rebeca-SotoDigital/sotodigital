<?php
declare(strict_types=1);

require __DIR__ . '/recovery-review.php';

$base = sys_get_temp_dir()
    . '/soto-recovery-review-'
    . bin2hex(random_bytes(6));

putenv('SOTO_QUEUE_DIR=' . $base . '/queue');
putenv('SOTO_JOURNAL_DIR=' . $base . '/journal');
putenv('SOTO_WHATSAPP_LIVE_SEND=0');

function comprobarRevision(
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

function crearMensajeRevision(
    string $id,
    string $estado
): void {
    sotoEncolarMensajeSeguro(
        '34999998888',
        $id,
        'Mensaje de prueba'
    );

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

function clasificacionDe(
    array $revision,
    string $id
): ?string {
    $ref = substr(hash('sha256', $id), 0, 12);

    foreach ($revision as $item) {
        if ($item['ref'] === $ref) {
            return $item['diagnostico'];
        }
    }

    return null;
}

try {
    echo "\n=== PRUEBAS DE RECUPERACIÓN ===\n\n";

    $id1 = 'wamid.REVIEW001';
    $id2 = 'wamid.REVIEW002';
    $id3 = 'wamid.REVIEW003';
    $id4 = 'wamid.REVIEW004';

    // Mensaje todavía pendiente.
    crearMensajeRevision($id1, 'pendiente');

    // PHP se interrumpió antes de iniciar un envío.
    crearMensajeRevision($id2, 'procesando');

    // PHP se interrumpió durante el envío.
    crearMensajeRevision($id3, 'procesando');

    sotoPrepararEnvio($id3, 'Respuesta simulada');
    sotoIniciarEnvio($id3);

    // Meta aceptó, pero la cola quedó incompleta.
    crearMensajeRevision($id4, 'procesando');

    sotoPrepararEnvio($id4, 'Otra respuesta');
    sotoIniciarEnvio($id4);

    sotoCerrarEnvio(
        $id4,
        'aceptado',
        'wamid.RESPUESTA004'
    );

    $revision = revisarRecuperacionCola();

    mostrarRevisionRecuperacion($revision);

    comprobarRevision(
        count($revision) === 4,
        'Se examinan los cuatro mensajes'
    );

    comprobarRevision(
        clasificacionDe($revision, $id1) ===
            'pendiente_normal',
        'Mensaje pendiente identificado'
    );

    comprobarRevision(
        clasificacionDe($revision, $id2) ===
            'revisar_sin_envio',
        'Interrupción sin diario identificada'
    );

    comprobarRevision(
        clasificacionDe($revision, $id3) ===
            'envio_incierto',
        'Envío incierto identificado'
    );

    comprobarRevision(
        clasificacionDe($revision, $id4) ===
            'revisar_finalizacion',
        'Aceptación sin finalizar identificada'
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
