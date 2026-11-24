<?php
declare(strict_types=1);

// Soto Digital - WhatsApp Webhook

ini_set('log_errors', '1');
ini_set('display_errors', '0');
ini_set('error_log', __DIR__ . '/errores-whatsapp.txt');
error_reporting(E_ALL);

function sotoLog(string $message): void
{
    error_log('SOTO_WHATSAPP: ' . $message);
}

function saveState($handle, string $status): bool
{
    $json = json_encode([
        'status' => $status,
        'updated_at' => gmdate('c')
    ]);

    if ($json === false) {
        return false;
    }

    rewind($handle);

    if (!ftruncate($handle, 0)) {
        return false;
    }

    $written = fwrite($handle, $json);
    fflush($handle);

    return $written === strlen($json);
}

$config = require __DIR__ . '/whatsapp-config.php';

$verifyToken = $config['verify_token'];
$appSecret = $config['app_secret'];
$accessToken = $config['access_token'];
$phoneNumberId = $config['phone_number_id'];

// Verificación de Meta
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';

    if (
        $mode === 'subscribe' &&
        is_string($token) &&
        hash_equals($verifyToken, $token)
    ) {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(200);
        echo $_GET['hub_challenge'] ?? '';
        exit;
    }

    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$raw = file_get_contents('php://input');

if ($raw === false) {
    http_response_code(400);
    exit;
}

// Validar autenticidad
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$expected = 'sha256=' . hash_hmac(
    'sha256',
    $raw,
    $appSecret
);

if (
    !is_string($signature) ||
    !hash_equals($expected, $signature)
) {
    http_response_code(403);
    exit;
}

$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    exit;
}

// Directorio privado mediante .htaccess
$directory = __DIR__ . '/processed';

if (!is_dir($directory)) {
    if (!mkdir($directory, 0700, true) && !is_dir($directory)) {
        sotoLog('ERROR: No se pudo crear processed');
        http_response_code(500);
        exit;
    }
}


/**
 * Limpieza automática de mensajes enviados.
 *
 * Se ejecuta ocasionalmente al recibir eventos
 * válidos de WhatsApp.
 *
 * Solo elimina estados "sent" de más de 30 días.
 */
try {
    if (random_int(1, 100) === 1) {

        $deleted = 0;
        $cutoff = time() - (30 * 24 * 60 * 60);

        foreach (new DirectoryIterator($directory) as $file) {

            if (!$file->isFile()) {
                continue;
            }

            $filename = $file->getFilename();

            // Solo archivos JSON de mensajes
            if (!preg_match('/^[a-f0-9]{64}\.json$/', $filename)) {
                continue;
            }

            $path = $file->getPathname();
            $handle = @fopen($path, 'r+');

            if ($handle === false) {
                continue;
            }

            // No tocar archivos en uso
            if (!flock($handle, LOCK_EX | LOCK_NB)) {
                fclose($handle);
                continue;
            }

            rewind($handle);
            $content = stream_get_contents($handle);
            $data = json_decode($content ?: '', true);

            if (
                is_array($data) &&
                ($data['status'] ?? '') === 'sent'
            ) {
                $updatedAt = $data['updated_at'] ?? '';
                $timestamp = is_string($updatedAt)
                    ? strtotime($updatedAt)
                    : false;

                if ($timestamp !== false && $timestamp < $cutoff) {
                    if (@unlink($path)) {
                        $deleted++;
                    }
                }
            }

            flock($handle, LOCK_UN);
            fclose($handle);
        }

        if ($deleted > 0) {
            sotoLog(
                'Limpieza automatica: ' .
                $deleted . ' registros eliminados'
            );
        }
    }
} catch (Throwable $e) {
    sotoLog('ERROR: Fallo en limpieza automatica');
}


$temporaryFailure = false;

foreach (($data['entry'] ?? []) as $entry) {
    foreach (($entry['changes'] ?? []) as $change) {

        $messages = $change['value']['messages'] ?? [];

        if (!is_array($messages)) {
            continue;
        }

        foreach ($messages as $message) {

            if (($message['type'] ?? '') !== 'text') {
                continue;
            }

            $from = $message['from'] ?? '';
            $messageId = $message['id'] ?? '';

            if (
                !is_string($from) || $from === '' ||
                !is_string($messageId) || $messageId === ''
            ) {
                continue;
            }

            // Nuevo formato: un JSON por mensaje
            $hash = hash('sha256', $messageId);
            $stateFile = $directory . '/' . $hash . '.json';

            $handle = @fopen($stateFile, 'c+');

            if ($handle === false) {
                sotoLog('ERROR: No se pudo abrir el estado');
                $temporaryFailure = true;
                continue;
            }

            // Evitar ejecuciones simultáneas
            if (!flock($handle, LOCK_EX | LOCK_NB)) {
                fclose($handle);
                sotoLog('Mensaje en procesamiento simultaneo');
                $temporaryFailure = true;
                continue;
            }

            rewind($handle);
            $stored = stream_get_contents($handle);
            $state = json_decode($stored ?: '', true);
            $currentStatus = is_array($state)
                ? ($state['status'] ?? '')
                : '';

            if ($currentStatus !== '') {
                sotoLog('Mensaje ya registrado: ' . $currentStatus);
                flock($handle, LOCK_UN);
                fclose($handle);
                continue;
            }

            // Marcar antes de llamar a Meta
            if (!saveState($handle, 'processing')) {
                sotoLog('ERROR: No se pudo guardar estado');
                flock($handle, LOCK_UN);
                fclose($handle);
                $temporaryFailure = true;
                continue;
            }

            $reply = [
                'messaging_product' => 'whatsapp',
                'to' => $from,
                'type' => 'text',
                'text' => [
                    'body' =>
                        "¡Hola! 👋 Bienvenido a Soto Digital.\n\n" .
                        "Soy el asistente virtual de la agencia.\n\n" .
                        "¿En qué podemos ayudarte?"
                ]
            ];

            $payload = json_encode(
                $reply,
                JSON_UNESCAPED_UNICODE
            );

            if ($payload === false) {
                saveState($handle, 'error');
                flock($handle, LOCK_UN);
                fclose($handle);
                continue;
            }

            $url = 'https://graph.facebook.com/v27.0/' .
                rawurlencode((string) $phoneNumberId) .
                '/messages';

            $ch = curl_init($url);

            if ($ch === false) {
                saveState($handle, 'error');
                flock($handle, LOCK_UN);
                fclose($handle);
                continue;
            }

            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 15
            ]);

            $response = curl_exec($ch);
            $status = (int) curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

            curl_close($ch);

            if ($response === false) {
                // No sabemos si Meta recibió el envío
                saveState($handle, 'uncertain');
                sotoLog('Envio incierto: revisar manualmente');

            } elseif ($status >= 200 && $status < 300) {
                saveState($handle, 'sent');
                sotoLog('Mensaje aceptado por Meta - HTTP ' . $status);

            } else {
                saveState($handle, 'error');

                $errorData = json_decode($response, true);
                $code = $errorData['error']['code'] ?? 'desconocido';

                sotoLog(
                    'Envio rechazado - HTTP ' . $status .
                    ' - Codigo Meta: ' . $code
                );
            }

            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}

http_response_code($temporaryFailure ? 503 : 200);
exit($temporaryFailure ? 'RETRY_LATER' : 'EVENT_RECEIVED');
