<?php
declare(strict_types=1);

/**
 * Configuración de ejecución del agente Soto Digital.
 *
 * Las claves nunca se guardan en GitHub.
 */

function iniciarRuntimeAgente(): array
{
    $config = require __DIR__ . '/whatsapp-config.php';

    if (!is_array($config)) {
        throw new RuntimeException(
            'Configuración privada inválida'
        );
    }

    $obligatorios = [
        'access_token',
        'phone_number_id',
        'anthropic_api_key',
        'agent_storage_dir',
        'worker_token'
    ];

    foreach ($obligatorios as $campo) {
        if (
            !is_string($config[$campo] ?? null) ||
            trim($config[$campo]) === ''
        ) {
            throw new RuntimeException(
                'Falta configuración: ' . $campo
            );
        }
    }

    if (($config['agent_enabled'] ?? false) !== true) {
        throw new RuntimeException(
            'El agente está desactivado'
        );
    }

    $base = $config['agent_storage_dir'];

    if (!str_starts_with($base, '/')) {
        throw new RuntimeException(
            'La ruta privada debe ser absoluta'
        );
    }

    $base = realpath($base);

    if ($base === false || !is_dir($base)) {
        throw new RuntimeException(
            'El almacenamiento privado no existe'
        );
    }

    $webRoot = realpath(
        (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')
    );

    $codeRoot = realpath(__DIR__);

    foreach ([$webRoot, $codeRoot] as $publico) {
        if (
            is_string($publico) &&
            $publico !== '' &&
            (
                $base === $publico ||
                str_starts_with(
                    $base,
                    rtrim($publico, '/') . '/'
                )
            )
        ) {
            throw new RuntimeException(
                'El almacenamiento debe estar fuera de la web'
            );
        }
    }

    if (!is_writable($base)) {
        throw new RuntimeException(
            'El almacenamiento no permite escritura'
        );
    }

    $directorios = [
        'SOTO_QUEUE_DIR' => 'queue',
        'SOTO_EVENTS_DIR' => 'events',
        'SOTO_JOURNAL_DIR' => 'journal',
        'SOTO_CONVERSATIONS_DIR' => 'conversations',
        'SOTO_PIPELINE_DIR' => 'pipeline',
        'SOTO_LEADS_DIR' => 'leads'
    ];

    foreach ($directorios as $variable => $carpeta) {
        $ruta = $base . '/' . $carpeta;

        if (!is_dir($ruta)) {
            $umaskAnterior = umask(0077);

            try {
                if (
                    !mkdir($ruta, 0700, true) &&
                    !is_dir($ruta)
                ) {
                    throw new RuntimeException(
                        'No se pudo crear un directorio privado'
                    );
                }
            } finally {
                umask($umaskAnterior);
            }
        }

        if (!is_writable($ruta)) {
            throw new RuntimeException(
                'Directorio privado no escribible'
            );
        }

        putenv($variable . '=' . $ruta);
    }

    putenv(
        'ANTHROPIC_API_KEY=' .
        $config['anthropic_api_key']
    );

    putenv(
        'WHATSAPP_ACCESS_TOKEN=' .
        $config['access_token']
    );

    putenv(
        'WHATSAPP_PHONE_NUMBER_ID=' .
        $config['phone_number_id']
    );

    putenv(
        'SOTO_WHATSAPP_LIVE_SEND=' .
        (
            ($config['agent_live_send'] ?? false) === true
                ? '1'
                : '0'
        )
    );

    return $config;
}
