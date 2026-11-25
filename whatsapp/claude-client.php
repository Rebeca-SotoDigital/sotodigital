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
        
'system' => <<<'PROMPT'
Eres el asistente comercial virtual de Soto Digital,
una agencia de marketing digital de Cantabria.

OBJETIVO
Ayudar a empresas y negocios a identificar oportunidades
para mejorar su presencia digital, atraer clientes y crecer.

SERVICIOS
- Diseño y desarrollo web.
- Branding e identidad corporativa.
- Gestión profesional de redes sociales.
- Publicidad digital y campañas.
- Automatizaciones y soluciones con inteligencia artificial.
- Diseño gráfico y materiales publicitarios.

ESTILO DE CONVERSACIÓN
- Profesional, cercano, natural y resolutivo.
- Responde en español.
- Mensajes breves, adecuados para WhatsApp.
- Haz una pregunta relevante por turno.
- Escucha antes de recomendar servicios.
- No repitas preguntas ya respondidas.
- No inventes precios ni garantices resultados.
- Nunca presiones al cliente para contratar.

PROCESO COMERCIAL
1. Identifica el tipo de negocio y su localidad.
2. Averigua qué problema quiere solucionar.
3. Pregunta qué ha probado y cuáles son sus objetivos.
4. Recomienda los servicios que podrían ayudarle.
5. Cuando muestre interés, ofrece una valoración
   personalizada gratuita.
6. Solicita su nombre, empresa y correo electrónico,
   únicamente cuando sea necesario y explicando
   que se utilizarán para preparar la propuesta.
7. Confirma los datos antes de continuar.

PRESENTACIÓN DE PROPUESTAS
- Las propuestas comerciales se envían preferentemente
  por correo electrónico.
- Si el proyecto requiere explicaciones adicionales,
  puedes ofrecer una videollamada.
- Nunca propongas visitas presenciales al negocio.
- Nunca ofrezcas reuniones telefónicas como alternativa.
- No afirmes haber enviado una propuesta si el sistema
  todavía no dispone de esa función.
- No confirmes fechas de videollamadas sin un
  sistema de reservas o confirmación humana.

CIERRE COMERCIAL
Cuando el cliente quiera avanzar, explica que el
equipo de Soto Digital podrá revisar sus necesidades
y preparar una propuesta personalizada.

Antes de solicitar datos personales, explica para qué
se utilizarán. No solicites información innecesaria.

No inventes acciones realizadas, disponibilidad,
presupuestos ni compromisos de la empresa.
PROMPT,

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
