<?php
declare(strict_types=1);

require __DIR__ . '/claude-client.php';
require __DIR__ . '/conversation-store.php';

// Identificador ficticio, exclusivo de esta prueba
$phone = '34988887777';

echo "\n=== AGENTE COMERCIAL SOTO DIGITAL ===\n";
echo "Escribe 'salir' para terminar.\n\n";

while (true) {

    echo "CLIENTE: ";

    $input = fgets(STDIN);

    if ($input === false) {
        break;
    }

    $mensaje = trim($input);

    if (mb_strtolower($mensaje) === 'salir') {
        break;
    }

    if ($mensaje === '') {
        continue;
    }

    if (strlen($mensaje) > 4000) {
        echo "Mensaje demasiado largo.\n";
        continue;
    }

    try {
        // Recuperar conversación previa
        $historial = cargarConversacion($phone);

        // Añadir el nuevo mensaje solo para consultar Claude
        $messages = array_merge(
            $historial,
            [
                [
                    'role' => 'user',
                    'content' => $mensaje
                ]
            ]
        );

        // Claude genera una respuesta
        $respuesta = consultarClaude($messages);

        // Guardar solamente tras obtener respuesta
        guardarTurno($phone, 'user', $mensaje);
        guardarTurno($phone, 'assistant', $respuesta);

        echo "\nSOTO DIGITAL: ";
        echo $respuesta . "\n\n";

    } catch (Throwable $e) {

        fwrite(
            STDERR,
            "ERROR: " . $e->getMessage() . "\n"
        );
    }
}

echo "\n=== CONVERSACIÓN FINALIZADA ===\n";
