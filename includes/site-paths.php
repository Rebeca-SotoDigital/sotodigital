<?php
/** Installation prefix inferred from the executed entry point, never from Host. */
function soto_base_path(): string {
    $root = realpath(__DIR__ . '/..');
    $file = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (PHP_SAPI === 'cli' && $script === ($_SERVER['SCRIPT_FILENAME'] ?? '')) return '';
    if (!$root || !$file || strpos($file, $root . DIRECTORY_SEPARATOR) !== 0) return '';
    $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($file, strlen($root)));
    if ($script === '' || substr($script, -strlen($relative)) !== $relative) return '';
    $base = substr($script, 0, strlen($script) - strlen($relative));
    return preg_match('#^(?:/[a-zA-Z0-9_.~-]+)*$#D', $base) ? $base : '';
}
function soto_path(string $path): string {
    return soto_base_path() . $path;
}
function soto_html_paths(string $html): string {
    $base = soto_base_path();
    if ($base === '') return $html;
    $html = preg_replace_callback('~\b(href|src|action|data-token-url)=("|\x27)/([^/][^"\x27]*|)(\2)~i', static function ($m) use ($base) {
        return $m[1] . '=' . $m[2] . htmlspecialchars($base, ENT_QUOTES, 'UTF-8') . '/' . $m[3] . $m[4];
    }, $html);
    return preg_replace_callback('~\bsrcset="([^"]*)"~i', static function ($m) use ($base) {
        return 'srcset="' . preg_replace('~(^|,\s*)/(?!/)~', '$1' . $base . '/', $m[1]) . '"';
    }, $html);
}
