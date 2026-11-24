<?php
declare(strict_types=1);

$config = require __DIR__ . '/whatsapp-config.php';

// No permitir ejecutar la prueba desde una URL GET.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Comprobar la clave privada de prueba.
$provided = $_POST['test_secret'] ?? '';

if (
    !is_string($provided) ||
    !hash_equals($config['test_secret'], $provided)
) {
    http_response_code(403);
    exit('Forbidden');
}

$accessToken = $config['access_token'];
$phoneNumberId = $config['phone_number_id'];

// Número previamente autorizado en Meta,
// con prefijo internacional y sin signo +
$destinatario = '34693420125';

$url = 'https://graph.facebook.com/v27.0/' .
    rawurlencode($phoneNumberId) . '/messages';

$mensaje = [
    'messaging_product' => 'whatsapp',
    'to' => $destinatario,
    'type' => 'template',
    'template' => [
        'name' => 'hello_world',
        'language' => ['code' => 'en_US']
    ]
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($mensaje),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20
]);

$respuesta = curl_exec($ch);
$codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: text/plain; charset=utf-8');

if ($respuesta === false) {
    http_response_code(502);
    exit('Error de conexión con Meta');
}

if ($codigo >= 200 && $codigo < 300) {
    echo 'Meta ha aceptado el envío.';
} else {
    http_response_code(502);
    echo 'Meta ha rechazado el envío. HTTP ' . $codigo;
}