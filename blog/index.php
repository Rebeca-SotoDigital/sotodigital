<?php
$pageTitle = 'Ideas de marketing para negocios | Soto Digital';
$pageDescription='Guías prácticas de Soto Digital sobre diseño web, SEO y Google Ads para decidir cómo conseguir más consultas para tu negocio.';
$canonicalPath='/blog/';
$posts=require __DIR__.'/../includes/blog-posts.php';
$blogEscape=static function($text){return htmlspecialchars($text,ENT_QUOTES,'UTF-8');};
include __DIR__.'/../includes/header.php';
?>
<main id="contenido" class="blog-page"><section class="blog-listing"><div class="container">
    <span class="section-subtitle">IDEAS PARA TU NEGOCIO</span>
    <h1>Decisiones digitales con más contexto.</h1>
    <p class="blog-intro">Guías para entender tu web, preparar tus campañas y elegir por dónde empezar.</p>
    <div class="blog-grid">
    <?php foreach($posts as $post):if(empty($post['approved']))continue; ?>
        <article class="blog-card"><p class="section-subtitle"><?= $blogEscape($post['category']) ?></p><h2><a href="/blog/<?= $blogEscape($post['slug']) ?>.php"><?= $blogEscape($post['title']) ?></a></h2><p><?= $blogEscape($post['summary']) ?></p><a class="blog-read" href="/blog/<?= $blogEscape($post['slug']) ?>.php" aria-label="Leer: <?= $blogEscape($post['title']) ?>">Leer artículo →</a></article>
    <?php endforeach; ?>
    </div>
    <aside class="blog-cta"><h2>Pasamos de las ideas a tu negocio.</h2><p>Cuéntanos qué necesitas y preparamos una propuesta gratuita y sin compromiso.</p><a class="btn" href="/#diagnostico"><?= SOTO_PROPOSAL_CTA ?></a></aside>
</div></section></main>
<?php include __DIR__.'/../includes/footer.php'; ?>
