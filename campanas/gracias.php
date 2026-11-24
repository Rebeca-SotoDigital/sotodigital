<?php
require __DIR__ . '/../includes/campaign-leads.php';
campaign_session();
header('X-Robots-Tag: noindex, nofollow');
if (empty($_SESSION['campaign_received']) && empty($_SESSION['campaign_screened'])) { header('Location: index.php', true, 302); exit; }
$receipt = $_SESSION['campaign_conversion'] ?? null;
unset($_SESSION['campaign_conversion']);
$slug = $receipt['service'] ?? 'estrategia-digital';
$campaignLanding = true;
$campaignSelf = '/campanas/index.php';
$pageTitle = 'Gracias por tu mensaje | Soto Digital';
$pageDescription = 'Hemos recibido tu solicitud de propuesta estratégica.';
$canonicalPath = '/campanas/gracias.php';
include __DIR__ . '/../includes/header.php';
?>
<main id="contenido">
    <section><div class="container campaign-thanks">
        <h1>¡Recibido! Te respondemos <?php if (soto_show_pending()): ?>en [PENDIENTE: plazo real, por ejemplo 24 horas laborables] <?php endif; ?>con una propuesta para tu negocio.</h1>
    </div></section>
</main>
<?php if ($receipt): ?><script id="soto-lead-receipt" type="application/json"><?= json_encode(['id' => $receipt['id']], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script><?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
