<?php
$details = require __DIR__ . '/project-details.php';
$projects = require __DIR__ . '/projects.php';
$detail = $details[$projectTheme];
$project = array_values(array_filter($projects, static function ($item) use ($projectTheme) { return $item['theme'] === $projectTheme; }))[0];
$pageTitle = $detail['title'] ?? $project['name'] . ' | Proyectos | Soto Digital';
$pageDescription = $detail['description'] ?? $detail['caseWork'];
$canonicalPath = '/' . $detail['slug'] . '.php';
$serviceStyles = true;
$escape = static function ($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); };
$pageStructuredData = [
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'CreativeWork', '@id' => 'https://soto-digital.com' . $canonicalPath . '#proyecto', 'url' => 'https://soto-digital.com' . $canonicalPath, 'name' => $project['name'], 'description' => $pageDescription, 'creator' => ['@id' => 'https://soto-digital.com/#negocio', '@type' => 'LocalBusiness', 'name' => 'Soto Digital'], 'about' => ['@type' => 'Organization', 'name' => $project['name'], 'url' => $project['web']]],
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
            <a class="text-link" href="/#proyectos">Volver a proyectos</a>
            <h1><?= $escape($project['name']) ?></h1>
            <p class="service-lead"><?= $escape($detail['phrase']) ?></p>
        </div>
    </section>
    <section class="case-detail">
        <div class="container">
            <?php if (soto_show_pending()): ?>
                <h2>Reto</h2>
                <p>[PENDIENTE]</p>
            <?php endif; ?>
            <h2>Qué hicimos</h2>
            <p><?= $escape($detail['caseWork']) ?></p>
            <?php if (soto_show_pending()): ?>
                <h2>Resultado</h2>
                <p>[PENDIENTE]</p>
                <?php if (in_array($projectTheme, ['refinish', 'kebab'], true)): ?>
                    <h2>Testimonio</h2>
                    <p>[PENDIENTE: testimonio del cliente]</p>
                <?php endif; ?>
                <h2>Capturas</h2>
                <div class="case-capture">[PENDIENTE: captura]</div>
            <?php endif; ?>
            <a class="btn btn-accent" href="/#diagnostico">Quiero algo así</a>
        </div>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
