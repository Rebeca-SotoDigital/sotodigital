<?php
declare(strict_types=1);
require_once __DIR__ . '/brevo-client.php';

/** Estado persistente: nunca reenviar automáticamente una notificación incierta. */
function sotoEstadoNotificacion(string $path, array $estado): void
{
    $tmp = tempnam(dirname($path), '.notify-');
    if ($tmp === false) { throw new RuntimeException('Error creando archivo temporal'); }
    try {
        chmod($tmp, 0600);
        $json = json_encode($estado, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (file_put_contents($tmp, $json, LOCK_EX) === false || !rename($tmp, $path)) {
            throw new RuntimeException('Error guardando estado de notificación');
        }
    } finally {
        if (is_file($tmp)) { unlink($tmp); }
    }
}

/** Procesa como máximo un nuevo lead confirmado, sin tocar la cola de WhatsApp. */
function sotoSincronizarUnLead(array $config, ?callable $contacto = null, ?callable $aviso = null): string
{
    foreach (['brevo_api_key', 'brevo_sender_email', 'lead_notification_email'] as $campo) {
        if (!is_string($config[$campo] ?? null) || trim($config[$campo]) === '') {
            throw new RuntimeException('Falta configuración privada de Brevo: ' . $campo);
        }
    }
    if (!filter_var($config['brevo_sender_email'], FILTER_VALIDATE_EMAIL)
        || !filter_var($config['lead_notification_email'], FILTER_VALIDATE_EMAIL)
        || (int) ($config['brevo_list_id'] ?? 0) !== 6) {
        throw new RuntimeException('Configuración de Brevo no válida');
    }
    $base = realpath((string) $config['agent_storage_dir']);
    if ($base === false) { throw new RuntimeException('Carpeta privada inexistente'); }
    $leads = $base . '/leads';
    $notificaciones = $base . '/notifications';
    if (!is_dir($notificaciones) && !mkdir($notificaciones, 0700, true) && !is_dir($notificaciones)) {
        throw new RuntimeException('No se pueden crear notificaciones');
    }
    $bloqueo = fopen($notificaciones . '/sync.lock', 'c');
    if ($bloqueo === false) { throw new RuntimeException('No se puede bloquear el sincronizador'); }
    try {
        if (!flock($bloqueo, LOCK_EX | LOCK_NB)) { return 'ocupado'; }
        $archivos = glob($leads . '/*.json') ?: [];
        sort($archivos, SORT_STRING);
        foreach ($archivos as $archivo) {
            $lead = json_decode((string) file_get_contents($archivo), true);
            if (!is_array($lead) || ($lead['confirmado'] ?? false) !== true || ($lead['consentimiento'] ?? false) !== true) {
                continue;
            }
            // Evita importar automáticamente antiguos contactos de prueba.
            $creado = strtotime((string) ($lead['creado_en'] ?? ''));
            $inicio = strtotime((string) ($config['brevo_start_at'] ?? ''));
            if ($creado === false || $inicio === false || $creado < $inicio) { continue; }
            $id = (string) ($lead['id'] ?? '');
            if (!preg_match('/^[a-f0-9]{64}$/', $id) || basename($archivo) !== $id . '.json') { continue; }
            $path = $notificaciones . '/' . $id . '.json';
            $estado = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];
            if (!is_array($estado)) { return 'revision'; }
            $fase = $estado['fase'] ?? 'nuevo';
            if (in_array($fase, ['completado', 'contacto_revision', 'email_revision', 'email_enviando'], true)) { continue; }
            if ($fase === 'nuevo') {
                sotoEstadoNotificacion($path, ['fase' => 'contacto_revision']);
                try {
                    ($contacto ?? 'sotoBrevoGuardarContacto')($config, $lead);
                } catch (Throwable $e) {
                    error_log('SOTO_BREVO_CONTACT_ERROR: ' . get_class($e));
                    return 'contacto_revision';
                }
                sotoEstadoNotificacion($path, ['fase' => 'contacto_ok']);
                $fase = 'contacto_ok';
            }
            if ($fase === 'contacto_ok') {
                // Estado previo: si el proceso cae durante la API de email,
                // un segundo disparo NO reenvía el aviso automáticamente.
                sotoEstadoNotificacion($path, ['fase' => 'email_enviando']);
                try {
                    $messageId = ($aviso ?? 'sotoBrevoAvisarLead')($config, $lead);
                } catch (Throwable $e) {
                    sotoEstadoNotificacion($path, ['fase' => 'email_revision']);
                    error_log('SOTO_BREVO_EMAIL_ERROR: ' . get_class($e));
                    return 'email_revision';
                }
                sotoEstadoNotificacion($path, ['fase' => 'completado', 'message_id' => (string) $messageId]);
                return 'completado';
            }
        }
        return 'sin_pendientes';
    } finally {
        flock($bloqueo, LOCK_UN);
        fclose($bloqueo);
    }
}
