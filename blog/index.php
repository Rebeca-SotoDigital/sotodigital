<?php
$pageTitle='Ideas de marketing para negocios | Soto Digital';
$pageDescription='Un espacio de Soto Digital para compartir ideas sobre web, contenidos y campañas para empresas y negocios.';
$canonicalPath='/blog/';$pageDraft=true;
include __DIR__.'/../includes/header.php';
?>
<main id="contenido"><section><div class="container"><span class="section-subtitle">IDEAS PARA TU NEGOCIO</span><h1>Decisiones digitales con más contexto.</h1><p>Web, contenido y campañas: un espacio para entender cómo se conectan con tu negocio.</p>
<?php $posts=require __DIR__.'/../includes/blog-posts.php';foreach($posts as $post):if(empty($post['approved']))continue; ?>
<article><h2><?= htmlspecialchars($post['title'],ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars($post['summary'],ENT_QUOTES,'UTF-8') ?></p></article>
<?php endforeach; ?>
<p><a class="btn" href="/#diagnostico">Cuéntanos qué necesita tu negocio</a></p></div></section></main>
<?php include __DIR__.'/../includes/footer.php'; ?>
