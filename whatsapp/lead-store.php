<?php
declare(strict_types=1);

/**
 * Almacenamiento de leads - Soto Digital
 * Versión inicial para pruebas locales.
 */

function leadDirectory(): string
{
    $directory = getenv('SOTO_LEADS_DIR');

    if (!$directory) {
        $directory = sys_get_temp_dir()
            . '/soto-digital-leads-dev';
    }

    if (!is_dir($directory)) {
        if (
            !mkdir($directory, 0700, true) &&
            !is_dir($directory)
        ) {
            throw new RuntimeException(
                'No se puede crear el directorio de leads'
            );
        }
    }

    if (!is_writable($directory)) {
        throw new RuntimeException(
            'El directorio no permite escritura'
        );
    }

    return $directory;
}

/**
 * Registra un lead confirmado por el cliente.
 *
 * No llamar a esta función únicamente porque
 * Claude detecte interés comercial.
 */
function guardarLead(
    string $clienteId,
    array $datos
): array {
    if (!preg_match('/^[0-9]{8,16}$/', $clienteId)) {
        throw new InvalidArgumentException(
            'Identificador de cliente no válido'
        );
    }

    if (($datos['confirmado'] ?? false) !== true) {
        throw new InvalidArgumentException(
            'Falta confirmación del cliente'
        );
    }

    if (($datos['consentimiento'] ?? false) !== true) {
        throw new InvalidArgumentException(
            'Falta aceptación para gestionar la solicitud'
        );
    }

    $nombre = trim((string) ($datos['nombre'] ?? ''));
    $negocio = trim((string) ($datos['negocio'] ?? ''));
    $email = trim((string) ($datos['email'] ?? ''));
    $necesidad = trim((string) ($datos['necesidad'] ?? ''));

    if (
        $nombre === '' ||
        $negocio === '' ||
        $necesidad === '' ||
        strlen($nombre) > 160 ||
        strlen($negocio) > 200 ||
        strlen($necesidad) > 2000
    ) {
        throw new InvalidArgumentException(
            'Datos comerciales incompletos o demasiado largos'
        );
    }

    if (
        strlen($email) > 254 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        throw new InvalidArgumentException(
            'Correo electrónico no válido'
        );
    }

    // Un registro por cliente en esta versión.
    // El hash no sustituye al cifrado de datos.
    $hash = hash('sha256', $clienteId);
    $file = leadDirectory() . '/' . $hash . '.json';

    $oldUmask = umask(0077);

    try {
        $handle = fopen($file, 'c+');
    } finally {
        umask($oldUmask);
    }

    if ($handle === false) {
        throw new RuntimeException(
            'No se pudo abrir el archivo del lead'
        );
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException(
                'No se pudo bloquear el archivo'
            );
        }

        rewind($handle);
        $existing = stream_get_contents($handle);
        $previous = json_decode($existing ?: '', true);

        // Repetir la misma confirmación no genera
        // automáticamente un segundo registro.
        if (
            is_array($previous) &&
            ($previous['estado'] ?? '') === 'pendiente'
        ) {
            return $previous;
        }

        $lead = [
            'id' => $hash,
            'nombre' => $nombre,
            'negocio' => $negocio,
            'email' => $email,
            'necesidad' => $necesidad,
            'estado' => 'pendiente',
            'canal' => 'whatsapp',
            'confirmado' => true,
            'consentimiento' => true,
            'creado_en' => gmdate('c')
        ];

        $json = json_encode(
            $lead,
            JSON_UNESCAPED_UNICODE |
            JSON_PRETTY_PRINT |
            JSON_THROW_ON_ERROR
        );

        rewind($handle);

        if (!ftruncate($handle, 0)) {
            throw new RuntimeException(
                'No se pudo actualizar el archivo'
            );
        }

        $written = fwrite($handle, $json);

        if ($written !== strlen($json)) {
            throw new RuntimeException(
                'No se pudo guardar el lead completo'
            );
        }

        fflush($handle);

        return $lead;

    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
