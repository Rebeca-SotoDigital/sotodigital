<?php
require __DIR__ . '/includes/campaign-context.php';
$slug = 'estrategia-digital';
$pageTitle = 'Agencia de marketing digital para empresas | Soto Digital';
$pageDescription = 'Diseño web, redes sociales, SEO, publicidad, email marketing y contenidos para empresas. Pide una propuesta gratuita de Soto Digital.';
$canonicalPath = '/';
$includeLocalBusiness = true;
include 'includes/header.php';
?>
<main id="contenido" class="home-page">
<section id="hero" class="new-hero">
    <div class="container">
        <div class="hero-copy">
            <span class="hero-context">Marketing digital y diseño web para empresas</span>
            <h1>Webs y campañas para recibir <span>más consultas.</span></h1>
            <p>Creamos la web, las campañas y la imagen que tu negocio necesita para que más gente te conozca y te escriba.</p>
            <div class="hero-buttons">
                <a href="#diagnostico" class="btn"><?= SOTO_PROPOSAL_CTA ?></a>
            </div>
        </div>
    </div>
</section>
<section id="soluciones">
    <div class="container">
        <div class="section-heading"><h2>¿Dónde estás? <span class="muted-heading">¿A dónde quieres llegar?</span></h2></div>
        <div class="solutions-grid">
            <a class="solution-card solution-dark" href="<?= $escape(SOTO_SERVICES['servicio-visibilidad'][1]) ?>"><h3>Quiero que <strong>me conozcan</strong></h3><p>Una presencia cuidada en redes y en internet para que más gente sepa que existes.</p></a>
            <a class="solution-card solution-dark" href="<?= $escape(SOTO_SERVICES['servicio-publicidad'][1]) ?>"><h3>Quiero <strong>más consultas</strong></h3><p>Una web clara y campañas que llevan a personas interesadas hasta tu puerta.</p></a>
            <a class="solution-card solution-dark" href="<?= $escape(SOTO_SERVICES['servicio-marca'][1]) ?>"><h3>Quiero una <strong>marca</strong> que destaque</h3><p>Una imagen coherente y reconocible que transmita confianza desde el primer vistazo.</p></a>
        </div>
    </div>
</section>
<section id="servicios" class="bg-soft">
    <div class="container">
        <div class="section-heading"><h2>Servicios</h2></div>
        <div class="service-list">
            <?php foreach (SOTO_SERVICES as $id => $entry): ?>
                <?php if (!isset($entry[2])) continue; ?>
                <a class="service-row" id="<?= $escape($id) ?>" href="<?= $escape($entry[1]) ?>" aria-labelledby="<?= $escape($id) ?>-titulo">
                    <?php $title = [
                        'servicio-web' => ['Webs', ' que abren puertas'],
                        'servicio-email-marketing' => ['Emails', ' que mantienen el contacto'],
                        'servicio-marketing-contenidos' => ['Contenido', ' que aporta valor'],
                    ][$id] ?? [$entry[0], $entry[2]]; ?>
                    <h3><span class="service-card-link"><span id="<?= $escape($id) ?>-titulo"><strong><?= $escape($title[0]) ?></strong><?= $escape($title[1]) ?></span><span class="service-card-more" aria-hidden="true">Ver más</span></span></h3>
                    <p><?= $escape($entry[3]) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="service-note"><p>¿No sabes cuál necesitas? Lo vemos juntos.</p><a class="btn" href="#diagnostico"><?= SOTO_PROPOSAL_CTA ?></a></div>
    </div>
</section>
<section id="diagnostico" class="bg-soft">
    <div class="container campaign-split">
        <div><h2>¿Qué puede mejorar en tu negocio?</h2><p>Respóndenos a unas preguntas y te enviamos una propuesta gratuita y sin compromiso.</p><p class="response-promise">Primera respuesta en un máximo de <?= $escape(campaign_response_time()) ?>.</p>
        <figure class="contact-process-video">
            <h3>¿Qué pasa cuando contactas con nosotros?</h3>
            <video controls muted playsinline preload="none" width="720" height="1280" data-viewport-video poster="/img/videos/que-pasa-cuando-contactas-portada.jpg" aria-label="Rebeca explica qué pasa después de contactar con Soto Digital">
                <source src="/img/videos/que-pasa-cuando-contactas.mp4" type="video/mp4">
                Tu navegador no permite reproducir el vídeo. <a href="/img/videos/que-pasa-cuando-contactas.mp4">Ver qué pasa después de contactar</a>.
            </video>
            <figcaption>Rebeca te explica el siguiente paso. Activa el sonido desde los controles para escucharla.</figcaption>
        </figure></div>
        <?php include __DIR__ . '/includes/campaign-form.php'; ?>
    </div>
</section>
<?php $projectFilter = 'all'; $projectPreviewLimit = 3; include __DIR__ . '/includes/project-section.php'; ?>
<section id="pantalla">
    <div class="container led-layout">
        <div class="led-photo led-recreation">
            <img src="/img/optimized/pantalla-led-recreacion-693.webp"
                 srcset="/img/optimized/pantalla-led-recreacion-420.webp 420w, /img/optimized/pantalla-led-recreacion-693.webp 693w"
                 sizes="(max-width: 700px) 100vw, 50vw" width="693" height="595"
                 alt="Recreación de la pantalla LED frente a la ITV del Polígono de Raos" loading="lazy">
        </div>
        <div class="led-copy">
            <span class="section-subtitle">Publicidad exterior</span>
            <h2>Pantalla <strong>LED</strong></h2>
            <p>Proyecto de publicidad exterior en una pantalla de <strong>6 × 3 metros</strong> en el Polígono de Raos, en Maliaño, Cantabria, frente a la ITV.</p>
            <p>Un soporte previsto para dar a conocer tu negocio o comunicar novedades en la zona. Podemos valorar el diseño o la adaptación de tu anuncio.</p>
            <p>Proyecto en preparación. Te informaremos cuando esté disponible. <a href="/pantalla-led-raos.php">Más detalles sobre la pantalla LED</a>.</p>
            <a class="btn btn-nav" href="/pantalla-led-raos.php#solicitud">Quiero información cuando esté disponible</a>
        </div>
    </div>
</section>
<section id="proceso" class="process-section">
    <div class="container">
        <div class="section-heading"><h2>Un equipo contigo. Un plan para avanzar.</h2></div>
        <div class="process-grid">
            <article><h3>1. Primero, tu negocio.</h3><p>Nos cuentas dónde estás, qué necesitas y qué quieres conseguir.</p></article>
            <article><h3>2. Un camino claro.</h3><p>Definimos las acciones, los servicios y una propuesta ajustada a tus objetivos.</p></article>
            <article><h3>3. Lo hacemos realidad.</h3><p>Creamos, ponemos en marcha y te acompañamos para seguir mejorando.</p></article>
        </div>
    </div>
</section>
<section id="contacto" class="contact-section">
    <div class="container">
        <h2>Hagamos que <span><strong>pase algo.</strong></span></h2>
        <p>Dinos dónde estás, cuéntanos qué buscas y nosotros te ayudamos a llegar.</p>
        <a href="#diagnostico" class="btn btn-accent"><?= SOTO_PROPOSAL_CTA ?></a>
    </div>
</section>
<?php $showTeamPresentation = true; include __DIR__.'/includes/team-section.php'; ?>
<section id="preguntas" class="faq-section">
    <div class="container faq-layout">
        <h2>Preguntas frecuentes</h2>
        <div class="faq-list">
            <details><summary>¿Puedo contratar un solo servicio?<span aria-hidden="true">+</span></summary><p>Sí. Puedes empezar por lo que más necesites, por ejemplo solo la web o solo las campañas, y ampliar cuando quieras.</p></details>
            <details><summary>No sé qué necesita mi negocio. ¿Me ayudáis?<span aria-hidden="true">+</span></summary><p>Claro. Cuéntanos en qué punto estás y te proponemos por dónde empezar, sin compromiso.</p></details>
            <details><summary>¿Podemos trabajar juntos desde otra ciudad?<span aria-hidden="true">+</span></summary><p>Sí. Trabajamos online con llamadas, videollamadas y mensajes, estés donde estés.</p></details>
            <details><summary>¿Cuánto cuesta trabajar con Soto Digital?<span aria-hidden="true">+</span></summary><p>Depende de lo que necesites, por eso preparamos una propuesta a medida y gratuita.<?php if (soto_show_pending()): ?> [PENDIENTE: precio de partida o rango, si lo decidimos]<?php endif; ?></p></details>
        </div>
    </div>
</section>
</main>
<?php include 'includes/footer.php'; ?>
