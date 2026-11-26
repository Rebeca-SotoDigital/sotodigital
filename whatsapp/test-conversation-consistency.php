<?php
declare(strict_types=1);

require __DIR__ . '/conversation-store.php';

$base = sys_get_temp_dir()
    . '/soto-consistency-test-'
    . bin2hex(random_bytes(6));

putenv('SOTO_CONVERSATIONS_DIR=' . $base);

function comprobarConsistencia(
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
    echo "\n=== PRUEBA MEMORIA CONSISTENTE ===\n\n";

    $phone = '34999998888';

    $id1 = 'wamid.CONSISTENCIA001';

    // 1. La memoria comienza vacía.
    comprobarConsistencia(
        count(cargarConversacion($phone)) === 0,
        'Historial inicialmente vacío'
    );

    // 2. Consolidar un intercambio.
    $r = guardarIntercambioConfirmado(
        $phone,
        $id1,
        'Tengo una cafetería',
        '¿Qué te gustaría mejorar de tu negocio?'
    );

    comprobarConsistencia(
        $r === 'guardado',
        'Intercambio almacenado'
    );

    $historial = cargarConversacion($phone);

    comprobarConsistencia(
        count($historial) === 2,
        'Se guardan dos turnos juntos'
    );

    comprobarConsistencia(
        $historial[0]['role'] === 'user' &&
        $historial[1]['role'] === 'assistant',
        'Los roles están ordenados'
    );

    // 3. El mismo evento no debe duplicarse.
    $r = guardarIntercambioConfirmado(
        $phone,
        $id1,
        'Tengo una cafetería',
        '¿Qué te gustaría mejorar de tu negocio?'
    );

    comprobarConsistencia(
        $r === 'duplicado',
        'Evento repetido identificado'
    );

    comprobarConsistencia(
        count(cargarConversacion($phone)) === 2,
        'No se duplica la memoria'
    );

    // 4. Segundo intercambio independiente.
    $r = guardarIntercambioConfirmado(
        $phone,
        'wamid.CONSISTENCIA002',
        'Necesito una web',
        'Podemos ayudarte con el diseño de tu web.'
    );

    comprobarConsistencia(
        $r === 'guardado',
        'Segundo intercambio almacenado'
    );

    $historial = cargarConversacion($phone);

    comprobarConsistencia(
        count($historial) === 4,
        'Se conservan los cuatro turnos'
    );

    comprobarConsistencia(
        $historial[2]['content'] ===
            'Necesito una web',
        'El segundo mensaje mantiene su contenido'
    );

    // 5. Probar el límite de memoria.
    for ($i = 3; $i <= 9; $i++) {
        guardarIntercambioConfirmado(
            $phone,
            'wamid.CONSISTENCIA' .
                str_pad((string) $i, 3, '0', STR_PAD_LEFT),
            'Pregunta ' . $i,
            'Respuesta ' . $i
        );
    }

    comprobarConsistencia(
        count(cargarConversacion($phone)) ===
            SOTO_HISTORY_LIMIT,
        'Se respeta el límite de 12 turnos'
    );

    // 6. Un evento antiguo sigue identificado,
    // aunque sus mensajes hayan salido del historial.
    $r = guardarIntercambioConfirmado(
        $phone,
        $id1,
        'Tengo una cafetería',
        '¿Qué te gustaría mejorar de tu negocio?'
    );

    comprobarConsistencia(
        $r === 'duplicado',
        'El índice reconoce eventos antiguos'
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
