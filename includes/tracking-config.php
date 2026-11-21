<?php
require_once __DIR__ . '/campaign-config.php';
$gaId = campaign_config('GOOGLE_ANALYTICS_ID');
$adsId = campaign_config('GOOGLE_ADS_ID');
$leadLabel = campaign_config('GOOGLE_ADS_LEAD_LABEL');
$whatsappLabel = campaign_config('GOOGLE_ADS_WHATSAPP_LABEL');
$trackingConfig = [
 'ga4' => preg_match('/\AG-[A-Z0-9]+\z/', $gaId) ? $gaId : '',
 'ads' => preg_match('/\AAW-[0-9]+\z/', $adsId) ? $adsId : '',
 'leadLabel' => preg_match('/\A[A-Za-z0-9_-]+\z/', $leadLabel) ? $leadLabel : '',
 'whatsappLabel' => preg_match('/\A[A-Za-z0-9_-]+\z/', $whatsappLabel) ? $whatsappLabel : '',
 'service' => isset($slug) && preg_match('/\A[a-z-]+\z/', $slug) ? $slug : 'general',
];
?>
<script id="soto-tracking-config" type="application/json"><?= json_encode($trackingConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
