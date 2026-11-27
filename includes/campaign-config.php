<?php
/** Optional private PHP file returning an array; environment variables take precedence. */
function campaign_config(string $key, $default = '') {
    static $private = null;
    if ($private === null) {
        $private = [];
        $path = getenv('SOTO_CONFIG_FILE');
        // Apache/FastCGI may expose rewrite variables only through $_SERVER,
        // or prefix them after an internal redirect. Never use request headers.
        if (!$path) {
            foreach (['SOTO_CONFIG_FILE', 'REDIRECT_SOTO_CONFIG_FILE', 'REDIRECT_REDIRECT_SOTO_CONFIG_FILE'] as $name) {
                $candidate = getenv($name) ?: ($_SERVER[$name] ?? '');
                if (is_string($candidate) && $candidate !== '') { $path = $candidate; break; }
            }
        }
        // Arsys layout: html/includes/ and its private sibling data/.
        // This fixed path also works when FastCGI drops Apache environment vars.
        if (!$path) {
            $candidate = dirname(__DIR__, 2) . '/data/soto-private/soto-config.php';
            if (is_file($candidate)) $path = $candidate;
        }
        if ($path) {
            $real = realpath($path);
            $roots = array_filter([realpath(__DIR__ . '/..'), realpath($_SERVER['DOCUMENT_ROOT'] ?? '')]);
            if (!$real || !is_file($real) || !is_readable($real)) throw new RuntimeException('Private configuration unavailable');
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
    // Reject placeholders without sending them to the provider.
    if ($key === 'BREVO_API_KEY' && !preg_match('/\Axkeysib-[A-Za-z0-9_-]+\z/', (string)$resolved)) return '';
    return $resolved;
}

/** Approved first-response window; empty private values use the agreed default. */
