<?php
// Public tracker URL from Leadfeeder, not an API key. Empty means disabled.
$companyTrackerUrl = getenv('LEADFEEDER_TRACKER_URL') ?: '';
$companyTrackerEnabled = (bool) preg_match('~\Ahttps://sc\.lftracker\.com/[^\s"<>]+\.js\z~D', $companyTrackerUrl);
?>
<?php if ($companyTrackerEnabled): ?>
<script id="company-identification-config" type="application/json"><?= json_encode(['url' => $companyTrackerUrl], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php endif; ?>
