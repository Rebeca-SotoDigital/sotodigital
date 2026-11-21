<?php
// EXAMPLE ONLY: copy OUTSIDE the public web root as soto-config.php.
// Point SOTO_CONFIG_FILE to that absolute path. Do not enter real secrets here.
return [
    'CAMPAIGN_DATA_DIR' => '/absolute/private/soto-leads',
    'TURNSTILE_SITE_KEY' => '',
    'TURNSTILE_SECRET_KEY' => '',
    'CAMPAIGN_ALLOWED_HOSTS' => 'soto-digital.com,www.soto-digital.com',
    'CAMPAIGN_RATE_SECRET' => '', // Optional independent random secret; fallback is Turnstile secret.
    'BREVO_API_KEY' => '',
    'BREVO_LIST_ID' => 0,
    'CAMPAIGN_MAIL_FROM' => 'ads@soto-digital.com', // Must be authorized by Brevo.
    'CAMPAIGN_MAIL_TO' => 'ads@soto-digital.com',
    'ANTHROPIC_API_KEY' => '',
    'ANTHROPIC_MODEL' => 'claude-haiku-5-5',
    'GOOGLE_ANALYTICS_ID' => '', // G-...
    'GOOGLE_ADS_ID' => '', // AW-...
    'GOOGLE_ADS_LEAD_LABEL' => '',
    'GOOGLE_ADS_WHATSAPP_LABEL' => '',
];
