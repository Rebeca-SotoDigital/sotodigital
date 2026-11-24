<?php
declare(strict_types=1);

/**
 * Cliente Anthropic para Soto Digital.
 * No envía mensajes de WhatsApp.
 */
function consultarClaude(array $messages): string
{
    $apiKey = getenv('ANTHROPIC_API_KEY');

    if (!$apiKey) {
        throw new RuntimeException(
            'No está configurada ANTHROPIC_API_KEY'
        );
    }

    if (empty($messages)) {
        throw new InvalidArgumentException(
            'La conversación está vacía'
        );
    }

    $payload = [
        'model' => 'claude-sonnet-4-6',
        'max_tokens' => 350,
        'system' =>
            'Eres el asistente comercial virtual de Soto Digital, ' .
            'una agencia de marketing digital de Cantabria. ' .
            'Ayudas a negocios a mejorar su presencia digital, ' .
            'atraer clientes y aumentar oportunidades comerciales. ' .
            'Ofrecemos diseño web, branding, redes sociales, ' .
            'publicidad digital y automatizaciones con IA. ' .
            'Responde en español, de forma profesional, cercana ' .
            'y natural. Haz una pregunta relevante por turno. ' .
            'No inventes precios, resultados garantizados ni ' .
            'servicios que no estén confirmados. ' .
            'Si el cliente quiere avanzar, ofrece una valoración ' .
            'personalizada gratuita sin presionarlo.',
        'messages' => $messages
    ];

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    $ch = curl_init(
        'https://api.anthropic.com/v1/messages'
    );

    if ($ch === false) {
        throw new RuntimeException(
            'No se pudo iniciar cURL'
        );
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'content-type: application/json'
        ],
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $status = (int) curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );
    $curlError = curl_error($ch);

    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException(
            'Fallo de conexión con Anthropic: ' . $curlError
        );
    }

    $result = json_decode($response, true);

    if ($status !== 200) {
        throw new RuntimeException(
            'Anthropic devolvió HTTP ' . $status
        );
    }

    if (!is_array($result)) {
        throw new RuntimeException(
            'Respuesta JSON no válida'
        );
    }

    $texts = [];

    foreach (($result['content'] ?? []) as $block) {
        if (($block['type'] ?? '') === 'text') {
            $texts[] = $block['text'];
        }
    }

    $text = trim(implode("\n", $texts));

    if ($text === '') {
        throw new RuntimeException(
            'Claude no devolvió contenido de texto'
        );
    }

    return $text;
}
