<?php
declare(strict_types=1);

require __DIR__ . '/lead-extractor.php';

$casos = [
    'Cliente interesado' => [
        [
            'role' => 'user',
            'content' =>
                'Hola, soy Laura. Tengo la Cafetería Nube ' .
                'en Santander y quiero conseguir más clientes.'
        ],
        [
            'role' => 'assistant',
            'content' =>
                '¿Qué necesitas mejorar ahora mismo?'
        ],
        [
            'role' => 'user',
            'content' =>
                'Necesito que gestionéis Instagram. ' .
                'Mi email es laura@example.com.'
        ],
        [
            'role' => 'assistant',
            'content' =>
                '¿Quieres recibir una valoración gratuita ' .
                'por email? Utilizaremos tus datos para ' .
                'preparar y atender esta solicitud.'
        ],
        [
            'role' => 'user',
            'content' =>
                'Sí, quiero recibir la propuesta por email. ' .
                'Autorizo que uséis mis datos para ' .
                'contactarme sobre esta solicitud.'
        ]
    ],
    'Solo pregunta información' => [
        [
            'role' => 'user',
            'content' =>
                'Hola, ¿hacéis páginas web?'
        ],
        [
            'role' => 'assistant',
            'content' =>
                'Sí, diseñamos páginas web adaptadas ' .
                'a cada negocio. ¿Qué necesitas?'
        ]
    ]
];

foreach ($casos as $nombre => $historial) {
    echo "\n=== $nombre ===\n";

    try {
        $datos = extraerLead($historial);

        echo json_encode(
            $datos,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        );

        echo "\n";
    } catch (Throwable $e) {
        echo 'ERROR: ' . $e->getMessage() . "\n";
    }
}
