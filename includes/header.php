<?php
require_once __DIR__ . '/site-paths.php';
require_once __DIR__ . '/site-content.php';
if (soto_base_path() !== '') ob_start('soto_html_paths');
$staging = soto_is_staging();
if ($staging) header('X-Robots-Tag: noindex, nofollow');
$pageTitle = $pageTitle ?? 'Soto Digital | Diseño web y marketing para empresas';
$pageDescription = $pageDescription ?? 'Diseño web, redes sociales, email marketing, marketing de contenidos, SEO, SEM, branding y publicidad para empresas y negocios con Soto Digital.';
$canonicalUrl = 'https://soto-digital.com' . ($canonicalPath ?? '/');
$siteAssets = require __DIR__ . '/asset-manifest.php';
$stylesheet = $siteAssets[!empty($serviceStyles) ? 'css/site-services.css' : 'css/site.css'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php if ($staging || !empty($campaignLanding) || !empty($pageDraft)): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#142b53">
    <link rel="apple-touch-icon" href="/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="https://soto-digital.com/img/og-soto-digital-1200x630.jpg">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Soto Digital">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="https://soto-digital.com/img/og-soto-digital-1200x630.jpg">
    <meta property="og:image:alt" content="Soto Digital">
    <?php if (!empty($pageStructuredData)): ?>
    <script type="application/ld+json"><?= json_encode($pageStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <?php endif; ?>
    
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
    <link rel="preload" href="/<?= htmlspecialchars($siteAssets['fonts/montserrat-latin.woff2'], ENT_QUOTES, 'UTF-8') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/<?= htmlspecialchars($stylesheet, ENT_QUOTES, 'UTF-8') ?>">
    <?php if (!empty($leadForm) || !empty($campaignLanding)): ?><link rel="stylesheet" href="/<?= htmlspecialchars($siteAssets['css/campaign.css'], ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
    <?php include __DIR__ . '/tracking-config.php'; ?>
    <?php if (!empty($includeLocalBusiness)) { include __DIR__ . '/local-business.php'; } ?>
</head>
<body<?= !empty($campaignLanding) ? ' class="campaign-body"' : '' ?>>

<a href="#contenido" class="skip-link">Saltar al contenido</a>
<header>
    <div class="container nav-container">
        <?php if (!empty($campaignLanding)): ?><span class="logo" aria-label="Soto Digital"><?php else: ?><a href="/" class="logo"><?php endif; ?>
            <img src="/img/optimized/logo-480.png" srcset="/img/optimized/logo-240.png 240w, /img/optimized/logo-480.png 480w" sizes="(max-width: 700px) 205px, (max-width: 992px) 218px, (max-width: 1150px) 205px, 240px" width="480" height="136" alt="SOTO DIGITAL Logo">
        <?php if (!empty($campaignLanding)): ?></span><?php else: ?></a><?php endif; ?>

        <?php if (!empty($campaignLanding)): ?>
        <a class="btn btn-nav" href="#propuesta"><?= SOTO_PROPOSAL_SHORT_CTA ?></a>
        <?php else: ?>
        <button class="mobile-toggle" id="mobile-menu" type="button" aria-expanded="false" aria-controls="nav-links" aria-label="Abrir menú">
            <i class="icon icon-bars" aria-hidden="true"></i>
        </button>

        <nav class="nav-links" id="nav-links" aria-label="Navegación principal">
            <a href="/#servicios">Servicios</a>
            <a href="/#proyectos">Proyectos</a>
            <a href="/#pantalla">Pantalla LED</a>
            <a href="/#contacto">Contacto</a>
            <a href="/#diagnostico" class="btn btn-nav"><?= SOTO_PROPOSAL_SHORT_CTA ?></a>
        </nav>
        <?php endif; ?>
    </div>
</header>
