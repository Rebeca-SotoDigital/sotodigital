<?php
declare(strict_types=1);

require __DIR__ . '/claude-client.php';

$messages = [
    [
        'role' => 'user',
        'content' =>
            'Hola, tengo una peluquería en Santander y ' .
            'quiero conseguir más clientes.'
    ],
    [
        'role' => 'assistant',
        'content' =>
            '¡Encantado de ayudarte! ¿Qué es lo que ' .
            'más te cuesta actualmente para atraer clientes?'
    ],
    [
        'role' => 'user',
        'content' =>
            'Publico en Instagram, pero casi nadie ' .
            'me pide cita.'
    ]
];

try {
    $respuesta = consultarClaude($messages);

    echo "\n=== AGENTE SOTO DIGITAL ===\n\n";
    echo $respuesta . "\n";
    echo "\n=== FIN DE PRUEBA ===\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        "ERROR: " . $e->getMessage() . "\n"
    );
    exit(1);
}
