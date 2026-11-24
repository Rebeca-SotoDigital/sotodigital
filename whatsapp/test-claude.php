<?php
declare(strict_types=1);

$apiKey = getenv('ANTHROPIC_API_KEY');

if (!$apiKey) {
    exit("ERROR: Falta ANTHROPIC_API_KEY\n");
}

$url = 'https://api.anthropic.com/v1/messages';

$data = [
    'model' => 'claude-sonnet-4-6',
    'max_tokens' => 350,
    'system' =>
        'Eres el asistente comercial de Soto Digital, ' .
        'una agencia de marketing digital de Cantabria. ' .
        'Responde de forma cercana, profesional y breve. ' .
        'Haz preguntas para entender las necesidades ' .
        'del cliente antes de recomendar servicios.',
    'messages' => [
        [
            'role' => 'user',
            'content' =>
                'Tengo una tienda en Santander y quiero ' .
                'conseguir más clientes mediante Instagram.'
        ]
    ]
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
        'content-type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($response === false) {
    exit("Error de conexion: $error\n");
}

$result = json_decode($response, true);

if ($status !== 200) {
    echo "Error HTTP: $status\n";
    echo $result['error']['message'] ?? 'Error desconocido';
    exit;
}

echo "\n=== CLAUDE - SOTO DIGITAL ===\n\n";

foreach (($result['content'] ?? []) as $block) {
    if (($block['type'] ?? '') === 'text') {
        echo $block['text'] . "\n";
    }
}

echo "\n=== PRUEBA FINALIZADA ===\n";
