<?php
require __DIR__ . '/../../includes/site-paths.php';
$root = realpath(__DIR__ . '/../..');
foreach (['', '/sotodigital'] as $prefix) {
    foreach (['/index.php', '/campanas/google-ads.php'] as $entry) {
        $_SERVER['SCRIPT_FILENAME'] = $root . $entry;
        $_SERVER['SCRIPT_NAME'] = $prefix . $entry;
        if (soto_base_path() !== $prefix) throw new RuntimeException('Incorrect installation prefix');
        $html = '<link href="/css/site.css"><img src="/img/logo.png" srcset="/img/a.png 1x, /img/b.png 2x"><form action="/api/lead.php" data-token-url="/api/token.php"><div data-action="proposal"></div><a href="https://example.com/">External</a>';
        $result = soto_html_paths($html);
        foreach (['href="'.$prefix.'/css/site.css"', 'action="'.$prefix.'/api/lead.php"', 'srcset="'.$prefix.'/img/a.png 1x, '.$prefix.'/img/b.png 2x"', 'data-action="proposal"', 'href="https://example.com/"'] as $expected) {
            if (strpos($result, $expected) === false) throw new RuntimeException('Path regression: ' . $expected);
        }
    }
}
echo "PASS: root and subdirectory asset, navigation, form and image paths; external URLs unchanged\n";
