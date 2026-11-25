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

/**
 * Ejecuta una operación sobre la conversación
 * bajo bloqueo exclusivo.
 *
 * Escribe en un temporal y sustituye el JSON
 * completo sin truncar el historial anterior.
 */
function withConversation(
    string $phone,
    callable $operation
): mixed {
    $path = conversationFile($phone);
    $lockPath = $path . '.lock';

    $oldUmask = umask(0077);

    try {
        $lock = fopen($lockPath, 'c');
    } finally {
        umask($oldUmask);
    }

    if ($lock === false) {
        throw new RuntimeException(
            'No se pudo abrir el bloqueo del historial'
        );
    }

    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException(
                'No se pudo bloquear la conversación'
            );
        }

        $state = [
            'updated_at' => time(),
            'messages' => []
        ];

        // Leer el JSON existente sin modificarlo.
        if (is_file($path)) {
            $raw = file_get_contents($path);

            if ($raw === false || trim($raw) === '') {
                throw new RuntimeException(
                    'Historial vacío o ilegible'
                );
            }

            $stored = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (
                !is_array($stored) ||
                !is_array($stored['messages'] ?? null)
            ) {
                throw new RuntimeException(
                    'Historial no válido'
                );
            }

            $updated = (int) (
                $stored['updated_at'] ?? 0
            );

            // Mantener la caducidad anterior.
            if (
                $updated > 0 &&
                time() - $updated < SOTO_HISTORY_TTL
            ) {
                $state = $stored;
            }
        }

        $result = $operation($state);

        if (!is_array($result) ||
            !array_key_exists('value', $result)) {
            throw new RuntimeException(
                'Operación de conversación inválida'
            );
        }

        if (empty($result['save'])) {
            return $result['value'];
        }

        if (
            !isset($result['state']) ||
            !is_array($result['state']) ||
            !is_array(
                $result['state']['messages'] ?? null
            )
        ) {
            throw new RuntimeException(
                'Nuevo estado de conversación inválido'
            );
        }

        $newState = $result['state'];
        $newState['updated_at'] = time();

        $json = json_encode(
            $newState,
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        );

        // Temporal en el mismo directorio.
        $temp = tempnam(
            dirname($path),
            '.conversation-'
        );

        if ($temp === false) {
            throw new RuntimeException(
                'No se pudo crear el archivo temporal'
            );
        }

        try {
            if (!chmod($temp, 0600)) {
                throw new RuntimeException(
                    'No se pudieron proteger los permisos'
                );
            }

            $handle = fopen($temp, 'wb');

            if ($handle === false) {
                throw new RuntimeException(
                    'No se pudo abrir el temporal'
                );
            }

            try {
                $length = strlen($json);
                $written = 0;

                while ($written < $length) {
                    $n = fwrite(
                        $handle,
                        substr($json, $written)
                    );

                    if ($n === false || $n === 0) {
                        throw new RuntimeException(
                            'Escritura incompleta'
                        );
                    }

                    $written += $n;
                }

                if (!fflush($handle)) {
                    throw new RuntimeException(
                        'No se pudo vaciar el búfer'
                    );
                }

                if (
                    function_exists('fsync') &&
                    !fsync($handle)
                ) {
                    throw new RuntimeException(
                        'No se pudo sincronizar el archivo'
                    );
                }
            } finally {
                fclose($handle);
            }

            // Comprobar el contenido escrito.
            $verification = file_get_contents($temp);

            if ($verification !== $json) {
                throw new RuntimeException(
                    'Verificación de escritura fallida'
                );
            }

            // Publicar la nueva versión del JSON.
            if (!rename($temp, $path)) {
                throw new RuntimeException(
                    'No se pudo publicar el historial'
                );
            }

        } finally {
            if (is_file($temp)) {
                unlink($temp);
            }
        }

        return $result['value'];

    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
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


/**
 * Consolida un intercambio completo.
 *
 * Debe llamarse SOLO después de confirmar
 * la aceptación del envío por Meta.
 *
 * Un mismo mensaje entrante solo puede
 * consolidarse una vez.
 *
 * Devuelve:
 * - 'guardado': intercambio incorporado
 * - 'duplicado': ya estaba registrado
 */
function guardarIntercambioConfirmado(
    string $phone,
    string $incomingId,
    string $textoCliente,
    string $respuestaAgente
): string {
    if (
        !str_starts_with($incomingId, 'wamid.') ||
        strlen($incomingId) < 8 ||
        strlen($incomingId) > 512
    ) {
        throw new InvalidArgumentException(
            'ID de WhatsApp no válido'
        );
    }

    $textoCliente = trim($textoCliente);
    $respuestaAgente = trim($respuestaAgente);

    foreach (
        [$textoCliente, $respuestaAgente] as $contenido
    ) {
        if (
            $contenido === '' ||
            strlen($contenido) > 4000
        ) {
            throw new InvalidArgumentException(
                'Contenido del intercambio no válido'
            );
        }
    }

    return withConversation(
        $phone,
        function (array $state) use (
            $incomingId,
            $textoCliente,
            $respuestaAgente
        ): array {
            $eventHash = hash('sha256', $incomingId);

            $registrados = $state['eventos_registrados'] ?? [];

            if (!is_array($registrados)) {
                throw new RuntimeException(
                    'Índice de eventos inválido'
                );
            }

            if (isset($registrados[$eventHash])) {
                return [
                    'save' => false,
                    'value' => 'duplicado'
                ];
            }

            $messages = $state['messages'] ?? [];

            if (!is_array($messages)) {
                throw new RuntimeException(
                    'Historial de mensajes inválido'
                );
            }

            // Incorporar ambos mensajes en una
            // única actualización del historial.
            $messages[] = [
                'role' => 'user',
                'content' => $textoCliente
            ];

            $messages[] = [
                'role' => 'assistant',
                'content' => $respuestaAgente
            ];

            $state['messages'] = array_slice(
                $messages,
                -SOTO_HISTORY_LIMIT
            );

            // Índice de idempotencia.
            // Se conserva con la conversación
            // hasta la caducidad configurada.
            $registrados[$eventHash] = time();

            $state['eventos_registrados'] = $registrados;

            return [
                'save' => true,
                'state' => $state,
                'value' => 'guardado'
            ];
        }
    );
}

