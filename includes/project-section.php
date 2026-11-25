<?php
$projects = require __DIR__ . '/projects.php';
$projectDetails = require __DIR__ . '/project-details.php';
$projectFilter = $projectFilter ?? 'all';
$projectItems = array_filter($projects, static function ($project) use ($projectFilter, $projectDetails) {
    return isset($projectDetails[$project['theme']]) && ($projectFilter === 'all' || in_array($projectFilter, $project['services'], true));
});
$projectEscape = static function ($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); };
$projectServiceNames = ['web' => 'Web', 'social' => 'Redes'];
?>
<section id="proyectos" class="projects-section">
    <div class="container">
        <div class="section-heading">
            <div><span class="section-subtitle">Trabajo real</span><h2>Proyectos con <strong>nombre propio</strong></h2></div>
            <p>Webs, redes y marcas que hemos creado para clientes y para nuestros propios negocios.</p>
        </div>
        <div class="projects-grid">
            <?php foreach ($projectItems as $project): ?>
                <?php
                $case = $projectDetails[$project['theme']];
                $cover = $case['cover'];
                ?>
                <article class="project-card project-theme-<?= $projectEscape($project['theme']) ?>">
                    <div class="project-cover">
                        <img src="/img/optimized/projects/<?= $projectEscape($cover['file']) ?>"
                             alt="<?= $projectEscape($cover['alt']) ?>"
                             width="<?= $cover['width'] ?>" height="<?= $cover['height'] ?>"
                             loading="lazy" decoding="async">
                    </div>
                    <div class="project-body">
                        <h3><?= $projectEscape($project['name']) ?></h3>
                        <p><?= $projectEscape($case['phrase']) ?></p>
                        <ul class="case-tags" aria-label="Servicios">
                            <?php foreach ($project['services'] as $projectService): ?><li><?= $projectEscape($projectServiceNames[$projectService]) ?></li><?php endforeach; ?>
                        </ul>
                        <a class="btn case-link" href="/<?= $projectEscape($case['slug']) ?>.php" aria-label="Ver proyecto de <?= $projectEscape($project['name']) ?>">Ver proyecto</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
