<?php
declare(strict_types=1);

/**
 * Protección de eventos WhatsApp - Soto Digital.
 *
 * - Evita reprocesar identificadores conocidos.
 * - Serializa la ejecución por cliente.
 * - Conserva estados inciertos para revisión.
 * - No envía WhatsApps ni llama a Claude.
 */

function guardDirectory(): string
{
    $dir = getenv('SOTO_EVENTS_DIR');

    if (!$dir) {
        $dir = sys_get_temp_dir()
            . '/soto-events-dev';
    }

    if (!is_dir($dir)) {
        $old = umask(0077);

        try {
            if (
                !mkdir($dir, 0700, true) &&
                !is_dir($dir)
            ) {
                throw new RuntimeException(
                    'No se pudo crear el directorio'
                );
            }
        } finally {
            umask($old);
        }
    }

    if (!is_writable($dir)) {
        throw new RuntimeException(
            'Directorio no escribible'
        );
    }

    return $dir;
}

/**
 * Escritura atómica del registro.
 */
function guardSave(string $path, array $data): void
{
    $json = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    $temp = tempnam(dirname($path), '.guard-');

    if ($temp === false) {
        throw new RuntimeException(
            'No se pudo crear archivo temporal'
        );
    }

    try {
        chmod($temp, 0600);

        if (file_put_contents($temp, $json) === false) {
            throw new RuntimeException(
                'No se pudo guardar el registro'
            );
        }

        if (!rename($temp, $path)) {
            throw new RuntimeException(
                'No se pudo actualizar el registro'
            );
        }
    } finally {
        if (is_file($temp)) {
            unlink($temp);
        }
    }
}

/**
 * Ejecuta una operación una sola vez por mensaje.
 *
 * $operacion debe devolver:
 * ['ok' => true] cuando toda su operación haya
 * terminado correctamente.
 *
 * Los fallos quedan pendientes de revisión.
 */
function procesarMensajeProtegido(
    string $telefono,
    string $messageId,
    callable $operacion
): array {
    if (!preg_match('/^[0-9]{8,16}$/', $telefono)) {
        throw new InvalidArgumentException(
            'Teléfono no válido'
        );
    }

    if (
        strlen($messageId) < 8 ||
        strlen($messageId) > 512
    ) {
        throw new InvalidArgumentException(
            'Identificador de mensaje no válido'
        );
    }

    $dir = guardDirectory();
    $clienteHash = hash('sha256', $telefono);
    $eventoHash = hash('sha256', $messageId);

    $lockPath = $dir . '/' . $clienteHash . '.lock';
    $dataPath = $dir . '/' . $clienteHash . '.json';

    $old = umask(0077);

    try {
        $handle = fopen($lockPath, 'c');
    } finally {
        umask($old);
    }

    if ($handle === false) {
        throw new RuntimeException(
            'No se pudo abrir el bloqueo'
        );
    }

    try {
        // Un único proceso por teléfono.
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException(
                'No se pudo obtener el bloqueo'
            );
        }

        $registro = [];

        if (is_file($dataPath)) {
            $raw = file_get_contents($dataPath);

            if ($raw === false) {
                throw new RuntimeException(
                    'No se pudo leer el registro'
                );
            }

            $registro = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!is_array($registro)) {
                throw new RuntimeException(
                    'Registro de eventos no válido'
                );
            }
        }

        // Limpiar solo eventos completados antiguos.
        $limite = time() - (30 * 86400);

        foreach ($registro as $id => $evento) {
            if (
                ($evento['estado'] ?? '') ===
                    'completado' &&
                ($evento['actualizado'] ?? 0) < $limite
            ) {
                unset($registro[$id]);
            }
        }

        // Cualquier evento conocido queda bloqueado,
        // incluso si terminó con un error incierto.
        if (isset($registro[$eventoHash])) {
            return [
                'estado' => 'duplicado',
                'estado_anterior' =>
                    $registro[$eventoHash]['estado'],
                'ejecutado' => false
            ];
        }

        // Registrar ANTES de procesar.
        $registro[$eventoHash] = [
            'estado' => 'procesando',
            'actualizado' => time()
        ];

        guardSave($dataPath, $registro);

        try {
            $resultado = $operacion();

            if (
                !is_array($resultado) ||
                ($resultado['ok'] ?? false) !== true
            ) {
                throw new RuntimeException(
                    'Operación no completada'
                );
            }

            $registro[$eventoHash] = [
                'estado' => 'completado',
                'actualizado' => time()
            ];

            guardSave($dataPath, $registro);

            return [
                'estado' => 'completado',
                'ejecutado' => true
            ];

        } catch (Throwable $e) {
            // El resultado de una operación externa
            // puede ser incierto. No reintentar a ciegas.
            $registro[$eventoHash] = [
                'estado' => 'revision',
                'actualizado' => time()
            ];

            guardSave($dataPath, $registro);

            return [
                'estado' => 'revision',
                'ejecutado' => true,
                'requiere_revision' => true
            ];
        }

    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
