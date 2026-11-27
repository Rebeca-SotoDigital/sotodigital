<?php
declare(strict_types=1);

/** Cliente HTTP mínimo para la API v3 de Brevo. Nunca registra claves ni datos personales. */
function sotoBrevoRequest(string $apiKey, string $endpoint, array $payload): array
{
    $ch = curl_init('https://api.brevo.com/v3/' . ltrim($endpoint, '/'));
    if ($ch === false) {
        throw new RuntimeException('No se pudo iniciar cURL');
    }
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'api-key: ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 20,
    ]);
    try {
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === false) {
            throw new RuntimeException('Fallo de transporte de Brevo');
        }
        return ['status' => $status, 'body' => (string) $body];
    } finally {
        curl_close($ch);
    }
}

function sotoBrevoGuardarContacto(array $config, array $lead): void
{
    $email = trim((string) ($lead['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Email de lead inválido');
    }
    // Solo utilizamos el atributo estándar FNAME. Los demás requerirán
    // atributos personalizados creados expresamente en Brevo.
    $attributes = [
        'FNAME' => (string) $lead['nombre'],
        // ORIGEN es un atributo de categoria en Brevo (ID verificado: 4).
        'ORIGEN' => 4,
    ];
    $telefono = (string) ($lead['telefono_contacto'] ?? '');
    if (preg_match('/^\+[1-9][0-9]{7,14}$/', $telefono)) {
        $attributes['SMS'] = $telefono;
    }
    if ((int) ($config['brevo_list_id'] ?? 0) !== 6) {
        throw new RuntimeException('La lista de leads de Brevo debe ser la 6');
    }
    $result = sotoBrevoRequest(
        (string) $config['brevo_api_key'],
        'contacts',
        [
            'email' => $email,
            'attributes' => $attributes,
            'listIds' => [(int) $config['brevo_list_id']],
            'updateEnabled' => true,
            // No dar permiso de campañas por solicitar una propuesta.
        ]
    );
    if (!in_array($result['status'], [201, 204], true)) {
        throw new RuntimeException('Brevo no aceptó el contacto (HTTP ' . $result['status'] . ')');
    }
}

function sotoBrevoAvisarLead(array $config, array $lead): string
{
    $escape = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $campos = [
        'Nombre' => (string) ($lead['nombre'] ?? ''),
        'Negocio' => (string) ($lead['negocio'] ?? ''),
        'Email' => (string) ($lead['email'] ?? ''),
        'Teléfono de contacto' => (string) ($lead['telefono_contacto'] ?? 'Pendiente de confirmar'),
        'Necesidad' => (string) ($lead['necesidad'] ?? ''),
        'Fecha' => (string) ($lead['creado_en'] ?? ''),
        'Origen' => 'WhatsApp',
    ];
    $html = '<html><body><h2>Nuevo lead confirmado — Soto Digital</h2><table cellpadding="8" border="1" cellspacing="0">';
    $texto = "Nuevo lead confirmado — Soto Digital\n\n";
    foreach ($campos as $etiqueta => $valor) {
        $html .= '<tr><th align="left">' . $escape($etiqueta) . '</th><td>' . nl2br($escape($valor)) . '</td></tr>';
        $texto .= $etiqueta . ': ' . $valor . "\n";
    }
    $html .= '</table><p>Notificación interna: no se ha enviado ninguna propuesta automáticamente.</p></body></html>';
    $result = sotoBrevoRequest(
        (string) $config['brevo_api_key'],
        'smtp/email',
        [
            'sender' => ['name' => 'Soto Digital - Agente IA', 'email' => (string) $config['brevo_sender_email']],
            'to' => [['email' => (string) $config['lead_notification_email']]],
            'subject' => 'Nuevo lead WhatsApp IA | ' . mb_substr((string) $lead['negocio'], 0, 100),
            'htmlContent' => $html,
            'textContent' => $texto,
        ]
    );
    $response = json_decode($result['body'], true);
    if ($result['status'] !== 201 || !is_array($response) || !is_string($response['messageId'] ?? null)) {
        throw new RuntimeException('Brevo no confirmó el aviso (HTTP ' . $result['status'] . ')');
    }
    return $response['messageId'];
}
