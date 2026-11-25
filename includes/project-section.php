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
            <?php $projectItems=array_values($projectItems); $projectLimit=isset($projectPreviewLimit)?max(1,(int)$projectPreviewLimit):count($projectItems); ?>
            <?php foreach ($projectItems as $projectIndex => $project): ?>
                <?php if ($projectIndex === $projectLimit): ?></div><details class="projects-more"><summary><span class="projects-show-label">Ver todos los proyectos (<?= count($projectItems) ?>)</span><span class="projects-hide-label">Ver menos</span></summary><div class="projects-grid"><?php endif; ?>
                <?php
                $case = $projectDetails[$project['theme']];
                $cover = $case['cover'];
                ?>
                <?php include __DIR__.'/project-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php if (count($projectItems)>$projectLimit): ?></details><?php endif; ?>
    </div>
</section>
