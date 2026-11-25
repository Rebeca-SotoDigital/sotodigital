<?php
$details = require __DIR__ . '/project-details.php';
$projects = require __DIR__ . '/projects.php';
$detail = $details[$projectTheme];
$serviceNames = ['web' => 'Web', 'social' => 'Redes'];
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
    <section class="service-intro project-intro project-theme-<?= $escape($projectTheme) ?>">
        <div class="container">
            <a class="text-link project-back" href="/#proyectos">Volver a proyectos</a>
            <div class="project-intro-copy">
                <h1><?= $escape($project['name']) ?></h1>
                <p class="service-lead"><?= $escape($detail['phrase']) ?></p>
                <ul class="case-tags" aria-label="Servicios">
                    <?php foreach ($project['services'] as $projectService): ?>
                        <?php $serviceUrl = $project[$projectService] ?? null; ?>
                        <li>
                            <?php if ($serviceUrl): ?>
                                <a href="<?= $escape($serviceUrl) ?>" target="_blank" rel="noopener noreferrer"
                                   aria-label="<?= $escape($serviceNames[$projectService]) ?> de <?= $escape($project['name']) ?> (se abre en una pestaña nueva)"><?= $escape($serviceNames[$projectService]) ?></a>
                            <?php else: ?>
                                <?= $escape($serviceNames[$projectService]) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>
    <section class="case-detail">
        <div class="container">
            <?php if (!empty($detail['challenge']) || soto_show_pending()): ?>
                <h2>Reto</h2>
                <?php foreach ($detail['challenge'] ?? ['[PENDIENTE]'] as $paragraph): ?>
                    <p><?= $escape($paragraph) ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
            <h2>Qué hicimos</h2>
            <?php foreach ($detail['workParagraphs'] ?? [$detail['caseWork']] as $paragraph): ?>
                <p><?= $escape($paragraph) ?></p>
            <?php endforeach; ?>
            <?php if (!empty($detail['result']) || soto_show_pending()): ?>
                <h2>Resultado</h2>
                <?php foreach ($detail['result'] ?? ['[PENDIENTE]'] as $paragraph): ?>
                    <p><?= $escape($paragraph) ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if (!empty($detail['testimonial'])): ?>
                <h2>Testimonio</h2>
                <blockquote><p><?= $escape($detail['testimonial']) ?></p></blockquote>
            <?php elseif (soto_show_pending() && in_array($projectTheme, ['refinish', 'kebab'], true)): ?>
                <h2>Testimonio</h2>
                <p>[PENDIENTE: testimonio del cliente]</p>
            <?php endif; ?>
            <?php if (!empty($detail['captures'])): ?>
                <h2>El proyecto en imágenes</h2>
                <div class="case-gallery">
                    <?php foreach ($detail['captures'] as $capture): ?>
                        <figure>
                            <img src="/img/optimized/projects/<?= $escape($capture['file']) ?>"
                                 width="<?= $capture['width'] ?>" height="<?= $capture['height'] ?>"
                                 alt="<?= $escape($capture['alt']) ?>" loading="lazy" decoding="async">
                            <figcaption><?= $escape($capture['caption']) ?></figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php elseif (soto_show_pending()): ?>
                <h2>El proyecto en imágenes</h2>
                <div class="case-capture">[PENDIENTE: captura]</div>
            <?php endif; ?>
            <a class="btn btn-accent" href="/#diagnostico">Quiero algo así</a>
        </div>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
