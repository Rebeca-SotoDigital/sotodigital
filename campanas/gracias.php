<?php
require __DIR__ . '/../includes/campaign-leads.php';
campaign_session();
header('X-Robots-Tag: noindex, nofollow');
$receipt = $_SESSION['campaign_conversion'] ?? null;
unset($_SESSION['campaign_conversion']);
$slug = $receipt['service'] ?? 'estrategia-digital';
$campaignLanding = true;
$campaignSelf = '/campanas/index.php';
$pageTitle = 'Gracias por tu mensaje | Soto Digital';
$pageDescription = 'Hemos recibido tu solicitud de propuesta estratégica.';
$canonicalPath = '/campanas/gracias.php';
// Page-local presentation overrides: shared header/footer remain unchanged.
require_once __DIR__.'/../includes/site-paths.php';
$thanksEscape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$senderEmail = campaign_config('CAMPAIGN_MAIL_FROM');
$senderEmail = filter_var($senderEmail, FILTER_VALIDATE_EMAIL) ? $senderEmail : '';
$thanksCss = soto_path('/campanas/gracias.css').'?v='.filemtime(__DIR__.'/gracias.css');
$thanksJs = soto_path('/campanas/gracias-tracking.js').'?v='.filemtime(__DIR__.'/gracias-tracking.js');
ob_start(static function ($html) use ($thanksEscape, $thanksCss, $thanksJs) {
    $home = $thanksEscape(soto_path('/'));
    $html = preg_replace('~<a class="btn btn-nav" href="#propuesta">.*?</a>~s', '<a class="btn btn-nav thanks-home" href="'.$home.'">Volver al inicio</a>', $html, 1);
    $html = str_replace('</head>', '<link rel="stylesheet" href="'.$thanksEscape($thanksCss).'">'."\n</head>", $html);
    // Use the page-specific copy of the existing consent-aware tracking only here.
    $html = preg_replace('~<script src="[^"<>]*/js/tracking(?:\.[a-f0-9]+)?\.js" defer></script>~', '<script src="'.$thanksEscape($thanksJs).'" defer></script>', $html, 1);
    return $html;
});
include __DIR__ . '/../includes/header.php';
?>
<main id="contenido" class="thanks-page">
    <section><div class="container campaign-thanks">
        <svg class="thanks-check" viewBox="0 0 64 64" width="72" height="72" aria-hidden="true" focusable="false"><circle cx="32" cy="32" r="29" fill="#e8f3f8" stroke="#1786af" stroke-width="2"/><path d="m19 32 9 9 18-19" fill="none" stroke="#142b53" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <h1>¡Recibido! Gracias por tu solicitud.</h1>
        <?php if ($slug === 'pantalla-led'): ?><p>Hemos registrado tu interés en la pantalla LED de Raos. El proyecto todavía no está disponible para contratar. Te informaremos cuando podamos confirmar disponibilidad y condiciones. No implica reserva ni pago.</p><?php endif; ?>
        <?php if ($slug !== 'pantalla-led'): ?><p>Hemos recibido tu solicitud y nuestro equipo la revisará con calma para preparar una propuesta pensada para tu negocio.</p><?php endif; ?>
        <?php if ($senderEmail !== ''): ?><p class="thanks-email">Revisa tu bandeja de entrada (y también la carpeta de spam) por si te escribimos</strong>.</p><?php else: ?><!-- TODO: confirmar CAMPAIGN_MAIL_FROM en la configuración privada para mostrar el remitente real. --><?php endif; ?>
        <aside class="thanks-next" aria-label="Conoce nuestro trabajo"><h2>Conoce lo que hacemos</h2><p>Mientras tanto, puedes ver algunos de nuestros proyectos de web, marca y comunicación.</p><a href="<?= $thanksEscape(soto_path('/#proyectos')) ?>">Ver nuestros proyectos <span aria-hidden="true">→</span></a></aside>
    </div></section>
</main>
<?php if ($receipt): ?><script id="soto-lead-receipt" type="application/json"><?= json_encode(['id' => $receipt['id']], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script><?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
