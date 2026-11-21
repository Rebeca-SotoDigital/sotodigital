<?php
http_response_code(404);
header('X-Robots-Tag: noindex, nofollow');
$pageDraft=true;
$pageTitle='Página no encontrada | Soto Digital';
$pageDescription='Encuentra los servicios de Soto Digital o cuéntanos qué necesita tu negocio.';
$canonicalPath='/404.php';
include __DIR__.'/includes/header.php';
?>
<main id="contenido"><section><div class="container"><span class="section-subtitle">PÁGINA NO ENCONTRADA</span><h1>Esta página no está disponible.</h1><p>Puede que el enlace haya cambiado. Puedes consultar nuestros servicios o enviarnos tu solicitud.</p><p><a class="btn" href="/#diagnostico">Pedir una propuesta</a></p><a class="text-link" href="/#servicios">Ver servicios</a></div></section></main>
<?php include __DIR__.'/includes/footer.php'; ?>
