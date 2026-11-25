<?php // Card shared by the preview and expanded portfolio. ?>
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
