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
        <h1>¡Recibido! Gracias por tu solicitud.</h1>
        <?php if ($slug === 'pantalla-led'): ?><p>Hemos registrado tu interés en la pantalla LED de Raos. El proyecto todavía no está disponible para contratar. Te informaremos cuando podamos confirmar disponibilidad y condiciones. No implica reserva ni pago.</p><?php endif; ?>
        <p>Recibirás una primera respuesta en un máximo de <strong><?= htmlspecialchars(campaign_response_time(), ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    </div></section>
</main>
<?php if ($receipt): ?><script id="soto-lead-receipt" type="application/json"><?= json_encode(['id' => $receipt['id']], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script><?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
