<?php
$pageTitle = $service['title'];
$pageDescription = $service['description'];
$canonicalPath = '/' . $service['slug'] . '.php';
$serviceStyles = true;
require __DIR__.'/campaign-context.php';
$slug = $service['form_service'] ?? ['seo-sem'=>'sem','pantalla-led-raos'=>'pantalla-led'][$service['slug']] ?? $service['slug'];
$pageDraft = !empty($service['draft']);
$escape = static function ($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); };
$contactUrl = '#solicitud';
$serviceLinks = [
    'diseno-web' => 'Diseño web',
    'redes-sociales' => 'Redes sociales',
    'branding' => 'Branding y diseño gráfico',
    'email-marketing' => 'Email marketing',
    'marketing-contenidos' => 'Marketing de contenidos',
    'seo-sem' => 'SEO y SEM',
    'publicidad-digital' => 'Campañas',
    'google-ads' => 'Google Ads',
    'meta-ads' => 'Instagram y Meta Ads',
    'pantalla-led-raos' => 'Pantalla LED en Raos',
];
$returnSections = [
    'diseno-web' => 'servicio-web',
    'redes-sociales' => 'servicio-visibilidad',
    'branding' => 'servicio-marca',
    'email-marketing' => 'servicio-email-marketing',
    'marketing-contenidos' => 'servicio-marketing-contenidos',
    'seo-sem' => 'servicio-seo-sem',
    'publicidad-digital' => 'servicio-publicidad',
    'google-ads' => 'servicio-seo-sem',
    'meta-ads' => 'servicio-publicidad',
    'pantalla-led-raos' => 'pantalla',
];
$returnUrl = '/#' . $returnSections[$service['slug']];
$returnLabel = $service['slug'] === 'pantalla-led-raos' ? 'Volver a la pantalla LED' : 'Volver a servicios';
$pageStructuredData = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            '@id' => 'https://soto-digital.com' . $canonicalPath . '#servicio',
            'name' => $service['heading'],
            'description' => $pageDescription,
            'url' => 'https://soto-digital.com' . $canonicalPath,
            'provider' => ['@type' => 'ProfessionalService', '@id' => 'https://soto-digital.com/#negocio', 'name' => 'Soto Digital', 'url' => 'https://soto-digital.com/'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => 'https://soto-digital.com/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $serviceLinks[$service['slug']], 'item' => 'https://soto-digital.com' . $canonicalPath],
            ],
        ],
    ],
];
include __DIR__ . '/header.php';
?>
<main id="contenido" class="service-page">
    <section class="service-intro">
        <div class="container">
            <a class="service-return" href="<?= $escape($returnUrl) ?>"><span aria-hidden="true">←</span> <?= $escape($returnLabel) ?></a>
            <nav class="service-breadcrumb" aria-label="Ruta de navegación"><a href="/">Inicio</a><span aria-hidden="true"> / </span><span aria-current="page"><?= $escape($serviceLinks[$service['slug']]) ?></span></nav>
            <span class="section-subtitle"><?= $escape($service['label']) ?></span>
            <h1><?php if (isset($service['heading_emphasis'])): ?><strong><?= $escape($service['heading_emphasis']) ?></strong><?= $escape(substr($service['heading'], strlen($service['heading_emphasis']))) ?><?php else: ?><?= $escape($service['heading']) ?><?php endif; ?></h1>
            <p class="service-lead"><?= $escape($service['intro']) ?></p>
            <a class="btn btn-accent" href="<?= $escape($contactUrl) ?>"><?= SOTO_PROPOSAL_CTA ?> <span aria-hidden="true">↗</span></a>
        </div>
    </section>
    <section>
        <div class="container">
            <div class="section-heading"><div><span class="section-subtitle">QUÉ PODEMOS HACER POR TI</span><h2><?= $escape($service['section']) ?></h2></div><?php if (!empty($service['body'])): ?><p><?= $escape($service['body']) ?></p><?php endif; ?></div>
            <?php if ($service['slug'] === 'pantalla-led-raos'): ?>
                <figure class="service-location"><img src="/img/optimized/pantalla-led-recreacion-693.webp" srcset="/img/optimized/pantalla-led-recreacion-420.webp 420w, /img/optimized/pantalla-led-recreacion-693.webp 693w" sizes="(max-width: 800px) 100vw, 50vw" width="693" height="595" alt="Recreación de la pantalla LED de Soto Digital frente a la ITV de Raos" loading="lazy"><figcaption>Recreación de la pantalla LED en Raos, Maliaño, Cantabria.</figcaption></figure>
            <?php endif; ?>
            <div class="process-grid">
                <?php foreach ($service['items'] as $item): ?>
                    <article><h3><?= $escape($item[0]) ?></h3><p><?= $escape($item[1]) ?></p></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php if (!empty($service['audience'])): ?>
    <section>
        <div class="container">
            <h2>Para quién es</h2>
            <p><?= $escape($service['audience']) ?></p>
        </div>
    </section>
    <?php endif; ?>
    <section class="bg-soft">
        <div class="container">
            <div class="section-heading"><div><span class="section-subtitle">UN PLAN CONTIGO</span><h2>Cómo trabajamos</h2></div><p>El alcance, los materiales y las condiciones se concretan en una propuesta ajustada a tu proyecto.</p></div>
            <div class="process-grid">
                <?php foreach ($service['steps'] as $number => $step): ?>
                    <article><span><?= sprintf('%02d', $number + 1) ?></span><h3><?= $escape($step[0]) ?></h3><?php if (isset($step[1])): ?><p><?= $escape($step[1]) ?></p><?php endif; ?></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php if (in_array($service['slug'], ['diseno-web', 'redes-sociales'], true)) {
        $projectFilter = $service['slug'] === 'diseno-web' ? 'web' : 'social';
        include __DIR__ . '/project-section.php';
    } ?>
    <?php if (!empty($service['faqs'])): ?>
    <section class="faq-section">
        <div class="container faq-layout">
            <div><span class="section-subtitle">RESOLVEMOS TUS DUDAS</span><h2>Antes de empezar</h2></div>
            <div class="faq-list">
                <?php foreach ($service['faqs'] as $faq): ?>
                    <details><summary><?= $escape($faq[0]) ?><span aria-hidden="true">+</span></summary><p><?= $escape($faq[1]) ?></p></details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    <section id="solicitud" class="campaign-contact">
        <div class="container campaign-split"><div><span class="section-subtitle">EL SIGUIENTE PASO</span><h2>Cuéntanos qué necesitas.</h2><p>Pide una propuesta gratuita. Revisaremos los datos que nos aportes y, según tus necesidades, te llamaremos si has dejado móvil o te enviaremos una propuesta por email.</p></div>
        <?php include __DIR__.'/campaign-form.php'; ?>
        </div>
    </section>
    <section class="service-related">
        <div class="container"><a class="service-return" href="<?= $escape($returnUrl) ?>"><span aria-hidden="true">←</span> <?= $escape($returnLabel) ?></a><h2>Más soluciones para tu negocio</h2><nav aria-label="Otros servicios">
            <?php foreach ($serviceLinks as $slug => $label): ?>
                <?php if ($slug !== $service['slug']): ?><a class="text-link" href="/<?= $escape($slug) ?>.php"><?= $escape($label) ?> <span aria-hidden="true">↗</span></a><?php endif; ?>
            <?php endforeach; ?>
        </nav></div>
    </section>
    <section class="campaign-contact service-close">
        <div class="container">
            <h2><?= $escape($service['closing_title'] ?? 'Hablemos de tu proyecto') ?></h2>
            <p><?= $escape($service['closing_text'] ?? 'Cuéntanos tu negocio y te enviamos una propuesta gratuita y sin compromiso.') ?></p>
            <a class="btn btn-accent" href="<?= $escape($contactUrl) ?>"><?= SOTO_PROPOSAL_CTA ?></a>
        </div>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
