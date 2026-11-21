<?php
$details = require __DIR__ . '/project-details.php';
$projects = require __DIR__ . '/projects.php';
$detail = $details[$projectTheme];
$project = array_values(array_filter($projects, static function ($item) use ($projectTheme) { return $item['theme'] === $projectTheme; }))[0];
$pageTitle = $detail['title'];
$pageDescription = $detail['description'];
$canonicalPath = '/' . $detail['slug'] . '.php';
$serviceStyles = true;
$escape = static function ($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); };
$relatedServices = ['web' => ['diseno-web.php', 'Diseño web para empresas'], 'social' => ['redes-sociales.php', 'Gestión de redes sociales para empresas']];
$pageStructuredData = [
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'CreativeWork', '@id' => 'https://soto-digital.com' . $canonicalPath . '#proyecto', 'url' => 'https://soto-digital.com' . $canonicalPath, 'name' => $detail['heading'], 'description' => $pageDescription, 'creator' => ['@id' => 'https://soto-digital.com/#negocio', '@type' => 'LocalBusiness', 'name' => 'Soto Digital'], 'about' => ['@type' => 'Organization', 'name' => $project['name'], 'url' => $project['web']]],
        ['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => 'https://soto-digital.com/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Proyectos', 'item' => 'https://soto-digital.com/#proyectos'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $project['name'], 'item' => 'https://soto-digital.com' . $canonicalPath],
        ]],
    ],
];
include __DIR__ . '/header.php';
?>
<main id="contenido" class="service-page project-page">
    <section class="service-intro">
        <div class="container">
            <a class="service-return" href="/#proyectos">← Volver a proyectos</a>
            <nav class="service-breadcrumb" aria-label="Ruta de navegación"><a href="/">Inicio</a><span aria-hidden="true">/</span><a href="/#proyectos">Proyectos</a><span aria-hidden="true">/</span><span aria-current="page"><?= $escape($project['name']) ?></span></nav>
            <span class="section-subtitle"><?= $escape($project['kind']) ?> / <?= $escape($project['sector']) ?></span>
            <h1><?= $escape($detail['heading']) ?></h1>
            <p class="service-lead"><?= $escape($detail['intro']) ?></p>
            <div class="project-links"><a class="text-link" href="<?= $escape($project['web']) ?>" target="_blank" rel="noopener noreferrer">Visitar la web del proyecto <span aria-hidden="true">↗</span></a><?php if ($project['social']): ?><a class="text-link" href="<?= $escape($project['social']) ?>" target="_blank" rel="noopener noreferrer">Ver Instagram <span aria-hidden="true">↗</span></a><?php endif; ?></div>
        </div>
    </section>
    <section><div class="container"><div class="section-heading"><div><span class="section-subtitle">EL NEGOCIO Y SU WEB</span><h2>Una presentación adaptada a su actividad</h2></div><p><?= $escape($detail['context']) ?></p></div><div class="process-grid"><?php foreach ($detail['work'] as $item): ?><article><h3><?= $escape($item[0]) ?></h3><p><?= $escape($item[1]) ?></p></article><?php endforeach; ?></div></div></section>
    <section class="bg-soft service-related"><div class="container"><span class="section-subtitle">SERVICIOS DEL PROYECTO</span><h2>Qué podemos hacer por tu negocio</h2><nav aria-label="Servicios relacionados con el proyecto"><?php foreach ($project['services'] as $key): ?><a class="text-link" href="<?= $escape($relatedServices[$key][0]) ?>"><?= $escape($relatedServices[$key][1]) ?> <span aria-hidden="true">↗</span></a><?php endforeach; ?></nav></div></section>
    <section class="contact-section"><div class="container"><span class="section-subtitle">HABLEMOS DE TU PROYECTO</span><h2>¿Qué necesita tu negocio?</h2><p>Cuéntanos tu actividad y qué quieres mejorar. Prepararemos una propuesta con el alcance y los servicios adecuados.</p><a class="btn btn-accent" href="/#diagnostico">Pedir una propuesta <span aria-hidden="true">↗</span></a></div></section>
    <section class="service-related"><div class="container"><h2>Otros proyectos</h2><nav aria-label="Otros proyectos"><?php foreach ($details as $theme => $other): ?><?php if ($theme !== $projectTheme): ?><a class="text-link" href="<?= $escape($other['slug']) ?>.php"><?= $escape($other['heading']) ?> <span aria-hidden="true">↗</span></a><?php endif; ?><?php endforeach; ?></nav></div></section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
