<?php
// EXAMPLE ONLY: copy OUTSIDE the public web root as soto-config.php.
// Point SOTO_CONFIG_FILE to that absolute path. Do not enter real secrets here.
return [
    'CAMPAIGN_DATA_DIR' => '/absolute/private/soto-leads',
    'CAMPAIGN_HEALTH_TOKEN' => '', // Long random token; send only via Authorization header.
    'CAMPAIGN_ALERT_TO' => 'ads@soto-digital.com',
    'TURNSTILE_SITE_KEY' => '',
    'TURNSTILE_SECRET_KEY' => '',
    'CAMPAIGN_RESPONSE_TIME' => '', // Only a service level approved by the team.
    'CAMPAIGN_PHONE_REQUIRED' => false,
    'CAMPAIGN_ALLOWED_HOSTS' => 'soto-digital.com,www.soto-digital.com',
    'CAMPAIGN_TRUSTED_PROXIES' => '', // Comma-separated CIDRs; proxies must overwrite forwarded headers.
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
];
