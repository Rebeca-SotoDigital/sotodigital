<?php
/** Optional private PHP file returning an array; environment variables take precedence. */
function campaign_config(string $key, $default = '') {
    static $private = null;
    if ($private === null) {
        $private = [];
        $path = getenv('SOTO_CONFIG_FILE');
        if ($path) {
            $real = realpath($path);
            $roots = array_filter([realpath(__DIR__ . '/..'), realpath($_SERVER['DOCUMENT_ROOT'] ?? '')]);
            if (!$real || !is_file($real)) throw new RuntimeException('Private configuration unavailable');
            foreach ($roots as $root) {
                if ($real === $root || strpos($real, $root . DIRECTORY_SEPARATOR) === 0) throw new RuntimeException('Configuration must be outside public directories');
            }
            $loaded = require $real;
            if (!is_array($loaded)) throw new RuntimeException('Invalid private configuration');
            $private = $loaded;
        }
    }
    $value = getenv($key);
    $resolved = $value !== false && $value !== '' ? $value : ($private[$key] ?? $default);
    // User-supplied dummy value must never be sent to Brevo.
    if ($key === 'BREVO_API_KEY' && in_array($resolved, ['contrasenasoto', 'CLAVEAPISOTO'], true)) return '';
    return $resolved;
}
