<?php
$projects = require __DIR__ . '/projects.php';
$projectDetails = require __DIR__ . '/project-details.php';
$projectFilter = $projectFilter ?? 'all';
$projectItems = array_filter($projects, static function ($project) use ($projectFilter) {
    return $projectFilter === 'all' ? $project['web'] !== null : in_array($projectFilter, $project['services'], true);
});
$projectEscape = static function ($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); };
$projectHeading = $projectFilter === 'social' ? 'Marcas cuyas redes gestionamos' : ($projectFilter === 'web' ? 'Webs que ya puedes visitar' : 'Proyectos con nombre propio');
?>
<section id="proyectos" class="projects-section">
    <div class="container">
        <div class="section-heading">
            <div><span class="section-subtitle">TRABAJO REAL</span><h2><?= $projectEscape($projectHeading) ?></h2></div>
            <p>Trabajamos con clientes y con nuestras propias marcas. Estos son algunos proyectos en los que llevamos la web, las redes sociales o ambas.</p>
        </div>
        <div class="projects-grid">
            <?php foreach ($projectItems as $project): ?>
                <?php $projectDetail = $projectDetails[$project['theme']] ?? null;
                $projectSummaryKey = $projectFilter === 'social' ? 'socialSummary' : ($projectFilter === 'web' ? 'webSummary' : null);
                $projectSummary = $projectSummaryKey ? ($projectDetail[$projectSummaryKey] ?? $project['description']) : $project['description']; ?>
                <article class="project-card project-theme-<?= $projectEscape($project['theme']) ?>">
                    <div class="project-cover" aria-hidden="true">
                        <span class="project-cover-grid"></span>
                        <span class="project-cover-label"><?= $projectEscape($project['sector']) ?></span>
                        <span class="project-cover-mark"><?php if (in_array($project['theme'], ['cars', 'wash'], true)): ?><span>SOTO</span><span><?= $project['theme'] === 'cars' ? 'CARS' : 'WASH' ?></span><?php elseif ($project['theme'] === 'carrocerias'): ?><span>Soto</span><span>Carrocerías</span><?php elseif ($project['theme'] === 'kebab'): ?><span>Cantabria</span><span>Kebab</span><?php else: ?><?= $projectEscape($project['mark']) ?><?php endif; ?></span>
                        <span class="project-cover-orbit"></span>
                        <span class="project-cover-caption"><?= $projectEscape($project['caption']) ?></span>
                        <span class="project-cover-arrow">↗</span>
                    </div>
                    <div class="project-body">
                    <div class="project-top"><span class="project-kind"><?= $projectEscape($project['kind']) ?></span><span class="project-scope"><?= in_array('web', $project['services'], true) ? 'WEB' : '' ?><?= count($project['services']) === 2 ? ' + ' : '' ?><?= in_array('social', $project['services'], true) ? 'REDES' : '' ?></span></div>
                    <h3><?= $projectEscape($project['name']) ?></h3>
                    <p class="project-sector"><?= $projectEscape($project['sector']) ?></p>
                    <p class="project-description"><?= $projectEscape($projectSummary) ?></p>
                    <div class="project-links">
                        <?php if ($projectDetail): ?><a class="text-link" href="<?= $projectEscape($projectDetail['slug']) ?>.php" aria-label="Conocer el proyecto de <?= $projectEscape($project['name']) ?>">Conocer el proyecto <span aria-hidden="true">→</span></a><?php endif; ?>
                        <?php if ($project['web'] && $projectFilter !== 'social'): ?><a class="text-link" href="<?= $projectEscape($project['web']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Visitar la web de <?= $projectEscape($project['name']) ?> (se abre en otra pestaña)">Visitar web <span aria-hidden="true">↗</span></a><?php endif; ?>
                        <?php if ($project['social']): ?><a class="text-link" href="<?= $projectEscape($project['social']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Ver Instagram de <?= $projectEscape($project['name']) ?> (se abre en otra pestaña)">Ver Instagram <span aria-hidden="true">↗</span></a><?php endif; ?>
                    </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($projectFilter === 'all'): ?><div class="project-more"><a class="text-link" href="/diseno-web.php#proyectos">Ver ejemplos de webs <span aria-hidden="true">↗</span></a><a class="text-link" href="/redes-sociales.php#proyectos">Ver ejemplos de redes sociales <span aria-hidden="true">↗</span></a></div><?php endif; ?>
    </div>
</section>
