<?php
declare(strict_types=1);

require __DIR__ . '/conversation-store.php';

$base = sys_get_temp_dir()
    . '/soto-atomic-test-'
    . bin2hex(random_bytes(6));

putenv('SOTO_CONVERSATIONS_DIR=' . $base);

$phone = '34999998888';

function verificarAtomic(
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
    echo "\n=== PRUEBAS MEMORIA ATÓMICA ===\n\n";

    verificarAtomic(
        cargarConversacion($phone) === [],
        'La conversación comienza vacía'
    );

    $id = 'wamid.ATOMIC001';

    $r = guardarIntercambioConfirmado(
        $phone,
        $id,
        'Necesito una página web',
        'Podemos ayudarte con tu proyecto.'
    );

    verificarAtomic(
        $r === 'guardado',
        'Primer intercambio guardado'
    );

    $path = conversationFile($phone);

    $original = file_get_contents($path);

    verificarAtomic(
        is_string($original) && $original !== '',
        'El archivo contiene el historial'
    );

    verificarAtomic(
        count(cargarConversacion($phone)) === 2,
        'Los dos mensajes están almacenados'
    );

    // Simular un fallo durante una operación.
    $fallo = false;

    try {
        withConversation(
            $phone,
            function (array $state): array {
                throw new RuntimeException(
                    'Fallo simulado antes de escribir'
                );
            }
        );
    } catch (RuntimeException $e) {
        $fallo = true;
    }

    verificarAtomic(
        $fallo,
        'Se detecta el fallo simulado'
    );

    verificarAtomic(
        file_get_contents($path) === $original,
        'El historial anterior no cambia'
    );

    // Comprobar la idempotencia.
    $r = guardarIntercambioConfirmado(
        $phone,
        $id,
        'Necesito una página web',
        'Podemos ayudarte con tu proyecto.'
    );

    verificarAtomic(
        $r === 'duplicado',
        'No duplica un intercambio'
    );

    verificarAtomic(
        count(cargarConversacion($phone)) === 2,
        'La memoria sigue teniendo dos turnos'
    );

    // Guardar un segundo intercambio.
    $r = guardarIntercambioConfirmado(
        $phone,
        'wamid.ATOMIC002',
        'También necesito gestionar Instagram',
        'Podemos estudiar tus redes sociales.'
    );

    verificarAtomic(
        $r === 'guardado',
        'Segundo intercambio guardado'
    );

    verificarAtomic(
        count(cargarConversacion($phone)) === 4,
        'Se conservan los cuatro turnos'
    );

    // Comprobar que el archivo sigue siendo
    // JSON válido después de las operaciones.
    $registro = json_decode(
        file_get_contents($path),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    verificarAtomic(
        count($registro['messages']) === 4,
        'El JSON final es válido'
    );

    verificarAtomic(
        is_file($path . '.lock'),
        'Existe un bloqueo independiente'
    );

    $temporales = glob(
        $base . '/.conversation-*'
    );

    verificarAtomic(
        is_array($temporales) &&
        count($temporales) === 0,
        'No quedan archivos temporales'
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
