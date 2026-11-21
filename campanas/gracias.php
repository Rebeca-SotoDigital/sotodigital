<?php
require __DIR__ . '/../includes/campaign-leads.php';
campaign_session();
header('X-Robots-Tag: noindex, nofollow');
if (empty($_SESSION['campaign_received']) && empty($_SESSION['campaign_screened'])) { header('Location: index.php', true, 302); exit; }
$receipt = $_SESSION['campaign_conversion'] ?? null;
unset($_SESSION['campaign_conversion']);
$slug = $receipt['service'] ?? 'estrategia-digital';
$campaignLanding = true;
$campaignSelf = 'campanas/index.php';
$pageTitle = 'Gracias por tu mensaje | Soto Digital';
$pageDescription = 'Hemos recibido tu solicitud de propuesta estratégica.';
$canonicalPath = '/campanas/gracias.php';
include __DIR__ . '/../includes/header.php';
?>
<main id="contenido"><section><div class="container campaign-thanks"><span class="section-subtitle">SOLICITUD RECIBIDA</span><h1>Gracias por<br>contarnos tu proyecto.</h1><p>Nos pondremos en contacto contigo lo antes posible por teléfono o email para conocer mejor tu negocio y preparar los siguientes pasos.</p><a class="btn" href="./">Conocer Soto Digital ↗</a></div></section></main>
<?php if ($receipt): ?><script id="soto-lead-receipt" type="application/json"><?= json_encode(['id' => $receipt['id']], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script><?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
