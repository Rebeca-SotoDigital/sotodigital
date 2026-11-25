<?php
declare(strict_types=1);

require __DIR__ . '/conversation-store.php';

// Número ficticio para pruebas
$phone = '34999999999';

echo "=== PRUEBA DE MEMORIA SOTO DIGITAL ===\n\n";

// Guardamos una conversación simulada
guardarTurno(
    $phone,
    'user',
    'Tengo una peluquería en Santander.'
);

guardarTurno(
    $phone,
    'assistant',
    '¿Qué te gustaría mejorar de tu negocio?'
);

guardarTurno(
    $phone,
    'user',
    'Quiero conseguir más reservas desde Instagram.'
);

// Recuperamos el historial
$historial = cargarConversacion($phone);

foreach ($historial as $mensaje) {
    echo strtoupper($mensaje['role']) . ': ';
    echo $mensaje['content'] . "\n\n";
}

echo 'Total de mensajes: ' . count($historial) . "\n";

echo "\n=== PRUEBA FINALIZADA ===\n";
