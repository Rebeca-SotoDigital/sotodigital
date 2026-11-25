<?php
declare(strict_types=1);

/**
 * Diario de envíos - Soto Digital.
 *
 * No envía WhatsApps.
 * No almacena el texto de los mensajes.
 * Cada entrada corresponde a un mensaje entrante.
 */

function sotoJournalDir(): string
{
    $dir = getenv('SOTO_JOURNAL_DIR');

    if (!$dir) {
        $dir = sys_get_temp_dir()
            . '/soto-journal-dev';
    }

    if (!is_dir($dir)) {
        $old = umask(0077);

        try {
            if (!mkdir($dir, 0700, true) &&
                !is_dir($dir)) {
                throw new RuntimeException(
                    'No se pudo crear el diario'
                );
            }
        } finally {
            umask($old);
        }
    }

    if (!is_writable($dir)) {
        throw new RuntimeException(
            'Diario no escribible'
        );
    }

    return $dir;
}

function sotoJournalUpdate(
    string $incomingId,
    callable $operation
): array {
    if (!str_starts_with($incomingId, 'wamid.') ||
        strlen($incomingId) > 512) {
        throw new InvalidArgumentException(
            'ID entrante no válido'
        );
    }

    $base = sotoJournalDir() . '/' .
        hash('sha256', $incomingId);

    $old = umask(0077);

    try {
        $lock = fopen($base . '.lock', 'c');
    } finally {
        umask($old);
    }

    if ($lock === false) {
        throw new RuntimeException(
            'No se pudo abrir el bloqueo'
        );
    }

    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException(
                'No se pudo bloquear el diario'
            );
        }

        $path = $base . '.json';
        $state = [];

        if (is_file($path)) {
            $raw = file_get_contents($path);

            if ($raw === false) {
                throw new RuntimeException(
                    'No se pudo leer el diario'
                );
            }

            $state = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!is_array($state)) {
                throw new RuntimeException(
                    'Registro inválido'
                );
            }
        }

        $updated = $operation($state);

        if (!is_array($updated)) {
            throw new RuntimeException(
                'Actualización inválida'
            );
        }

        $updated['actualizado_en'] = gmdate('c');

        $json = json_encode(
            $updated,
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        );

        $temp = tempnam(dirname($path), '.journal-');

        if ($temp === false) {
            throw new RuntimeException(
                'No se pudo crear archivo temporal'
            );
        }

        try {
            chmod($temp, 0600);

            $length = strlen($json);
            $written = file_put_contents($temp, $json);

            if ($written !== $length ||
                !rename($temp, $path)) {
                throw new RuntimeException(
                    'No se pudo guardar el diario'
                );
            }
        } finally {
            if (is_file($temp)) {
                unlink($temp);
            }
        }

        return $updated;

    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

/**
 * Prepara una respuesta sin almacenar su texto.
 */
function sotoPrepararEnvio(
    string $incomingId,
    string $respuesta
): array {
    if (trim($respuesta) === '') {
        throw new InvalidArgumentException(
            'Respuesta vacía'
        );
    }

    $hash = hash('sha256', $respuesta);

    return sotoJournalUpdate(
        $incomingId,
        function (array $state) use ($hash): array {
            if ($state !== []) {
                if (($state['respuesta_hash'] ?? '') !==
                    $hash) {
                    throw new RuntimeException(
                        'Ya existe otra respuesta para este evento'
                    );
                }

                return $state;
            }

            return [
                'estado' => 'preparado',
                'respuesta_hash' => $hash,
                'creado_en' => gmdate('c')
            ];
        }
    );
}

/**
 * Registra el comienzo del intento de envío.
 */
function sotoIniciarEnvio(string $incomingId): array
{
    return sotoJournalUpdate(
        $incomingId,
        function (array $state): array {
            if (($state['estado'] ?? '') !== 'preparado') {
                throw new RuntimeException(
                    'El envío no está preparado'
                );
            }

            $state['estado'] = 'enviando';
            $state['intento_en'] = gmdate('c');

            return $state;
        }
    );
}

/**
 * Registra la respuesta obtenida de Meta.
 *
 * $estado: aceptado, incierto o rechazado.
 */
function sotoCerrarEnvio(
    string $incomingId,
    string $estado,
    ?string $metaId = null
): array {
    if (!in_array(
        $estado,
        ['aceptado', 'incierto', 'rechazado'],
        true
    )) {
        throw new InvalidArgumentException(
            'Estado no permitido'
        );
    }

    if ($estado === 'aceptado' &&
        (!$metaId || !str_starts_with($metaId, 'wamid.'))) {
        throw new InvalidArgumentException(
            'Falta identificador de Meta'
        );
    }

    return sotoJournalUpdate(
        $incomingId,
        function (array $state) use (
            $estado,
            $metaId
        ): array {
            if (($state['estado'] ?? '') !== 'enviando') {
                throw new RuntimeException(
                    'No hay un envío abierto'
                );
            }

            $state['estado'] = $estado;

            if ($estado === 'aceptado') {
                $state['meta_message_id'] = $metaId;
            }

            return $state;
        }
    );
}
