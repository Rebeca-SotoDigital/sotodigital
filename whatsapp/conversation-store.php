<?php
declare(strict_types=1);

/**
 * Memoria de conversaciones - Soto Digital
 *
 * - Un archivo JSON por conversación
 * - Número anonimizado mediante SHA-256
 * - Máximo 12 mensajes
 * - Caducidad tras 7 días de inactividad
 * - Bloqueo de archivos para evitar escrituras simultáneas
 */

const SOTO_HISTORY_LIMIT = 12;
const SOTO_HISTORY_TTL = 7 * 24 * 60 * 60;

function conversationDirectory(): string
{
    $configured = getenv('SOTO_CONVERSATIONS_DIR');

    // Directorio temporal solo para desarrollo
    $directory = $configured ?: (
        sys_get_temp_dir() . '/soto-digital-conversations-dev'
    );

    if (!is_dir($directory)) {
        if (
            !mkdir($directory, 0700, true) &&
            !is_dir($directory)
        ) {
            throw new RuntimeException(
                'No se pudo crear el directorio de memoria'
            );
        }
    }

    if (!is_writable($directory)) {
        throw new RuntimeException(
            'El directorio de memoria no permite escritura'
        );
    }

    return $directory;
}

function conversationFile(string $phone): string
{
    if (!preg_match('/^[0-9]{8,16}$/', $phone)) {
        throw new InvalidArgumentException(
            'Identificador de teléfono no válido'
        );
    }

    return conversationDirectory() . '/' .
        hash('sha256', $phone) . '.json';
}

/**
 * Ejecuta una operación bajo bloqueo exclusivo.
 */
function withConversation(
    string $phone,
    callable $operation
): mixed {
    $path = conversationFile($phone);

    // Crear nuevos archivos con permisos privados
    $oldUmask = umask(0077);

    try {
        $handle = fopen($path, 'c+');
    } finally {
        umask($oldUmask);
    }

    if ($handle === false) {
        throw new RuntimeException(
            'No se pudo abrir el historial'
        );
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException(
                'No se pudo bloquear el historial'
            );
        }

        rewind($handle);
        $raw = stream_get_contents($handle);

        $state = [
            'updated_at' => time(),
            'messages' => []
        ];

        if ($raw !== false && trim($raw) !== '') {
            $stored = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!is_array($stored)) {
                throw new RuntimeException(
                    'Historial no válido'
                );
            }

            $updated = (int) ($stored['updated_at'] ?? 0);

            if (
                $updated > 0 &&
                time() - $updated < SOTO_HISTORY_TTL
            ) {
                $state = $stored;
            }
        }

        $result = $operation($state);

        if (!empty($result['save'])) {
            $state = $result['state'];
            $state['updated_at'] = time();

            $json = json_encode(
                $state,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );

            rewind($handle);

            if (!ftruncate($handle, 0)) {
                throw new RuntimeException(
                    'No se pudo actualizar el historial'
                );
            }

            $written = fwrite($handle, $json);

            if ($written !== strlen($json)) {
                throw new RuntimeException(
                    'No se pudo guardar el historial completo'
                );
            }

            fflush($handle);
        }

        return $result['value'];

    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

/**
 * Recupera el historial de un cliente.
 */
function cargarConversacion(string $phone): array
{
    return withConversation(
        $phone,
        function (array $state): array {
            return [
                'save' => false,
                'value' => $state['messages'] ?? []
            ];
        }
    );
}

/**
 * Añade un mensaje al historial.
 */
function guardarTurno(
    string $phone,
    string $role,
    string $content
): void {
    if (!in_array($role, ['user', 'assistant'], true)) {
        throw new InvalidArgumentException(
            'Rol no permitido'
        );
    }

    $content = trim($content);

    if ($content === '' || strlen($content) > 4000) {
        throw new InvalidArgumentException(
            'Longitud del mensaje no válida'
        );
    }

    withConversation(
        $phone,
        function (array $state) use ($role, $content): array {

            $messages = $state['messages'] ?? [];

            $messages[] = [
                'role' => $role,
                'content' => $content
            ];

            $state['messages'] = array_slice(
                $messages,
                -SOTO_HISTORY_LIMIT
            );

            return [
                'save' => true,
                'state' => $state,
                'value' => null
            ];
        }
    );
}
