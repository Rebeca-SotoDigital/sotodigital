<?php
// EXAMPLE ONLY: copy OUTSIDE the public web root as soto-config.php.
// Point SOTO_CONFIG_FILE to that absolute path. Do not enter real secrets here.
return [
    'SOTO_REGISTRO_MERCANTIL' => '', // Fill only with real company registration details.
    'CAMPAIGN_DATA_DIR' => '/absolute/private/soto-leads',
    'CAMPAIGN_RETENTION_MONTHS' => 12,
    'CAMPAIGN_BACKUP_KEEP' => 14,
    'CAMPAIGN_WORKER_MAX_AGE' => 900,
    'CAMPAIGN_PURGE_BREVO' => false, // Requires separate review: deletes a whole Brevo contact.
    'CAMPAIGN_HEALTH_TOKEN' => '', // Long random token; send only via Authorization header.
    'CAMPAIGN_PROCESS_ON_SUBMIT' => true, // Try CRM/emails after durable capture, max 15 seconds.
    'CAMPAIGN_WORKER_TOKEN' => '', // Independent random token >=32 chars; X-Soto-Worker-Token header only.
    'CAMPAIGN_ALERT_TO' => 'ads@soto-digital.com',
    'TURNSTILE_SITE_KEY' => '',
    'TURNSTILE_SECRET_KEY' => '',
    'CAMPAIGN_RESPONSE_TIME' => '2 días laborables', // Approved maximum for the first response, not delivery of the proposal.
    'CAMPAIGN_PHONE_REQUIRED' => false,
    'CAMPAIGN_ALLOWED_HOSTS' => 'soto-digital.com,www.soto-digital.com',
    'CAMPAIGN_TRUSTED_PROXIES' => '', // Comma-separated CIDRs; proxies must overwrite forwarded headers.
    'CAMPAIGN_RATE_SECRET' => '', // Optional independent random secret; fallback is Turnstile secret.
    'BREVO_API_KEY' => '',
    'BREVO_CREATE_DEALS' => false,
    'BREVO_PIPELINE_ID' => '',
    'BREVO_NEW_STAGE_ID' => '',
    'BREVO_LIST_ID' => 0,
    'CAMPAIGN_MAIL_FROM' => 'ads@soto-digital.com', // Must be authorized by Brevo.
    'CAMPAIGN_MAIL_TO' => 'ads@soto-digital.com',
    'CAMPAIGN_REQUIRE_AI' => false, // Optional AI must not fail Brevo/capture health.
    'ANTHROPIC_API_KEY' => '',
    'ANTHROPIC_MODEL' => 'claude-haiku-5-5',
    'GOOGLE_ANALYTICS_ID' => '', // G-...
    'GOOGLE_ADS_ID' => '', // AW-...
    'GOOGLE_ADS_LEAD_LABEL' => '',
];
