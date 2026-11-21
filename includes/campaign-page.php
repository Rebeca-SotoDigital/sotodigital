<?php
require __DIR__ . '/campaign-context.php';
header('X-Robots-Tag: noindex, nofollow');
$services = require __DIR__ . '/campaign-services.php';
$slug = $campaignSlug ?? 'estrategia-digital';
if (!isset($services[$slug])) { http_response_code(404); exit; }
$service = $services[$slug];
$variantKey = is_string($_GET['v'] ?? null) ? $_GET['v'] : '';
$campaignVariant = $service[5][$variantKey] ?? null;
if ($campaignVariant) {
    $service[1]=$campaignVariant['heading'];$service[2]=$campaignVariant['intro'];

}
$content = require __DIR__ . '/campaign-content.php';
$campaignSelf = '/campanas/' . ($slug === 'estrategia-digital' ? 'index.php' : $slug . '.php');
$campaignLanding = true;
$pageTitle = $service[0] . ' | Mini-auditoría gratuita | Soto Digital';
$pageDescription = $service[2];
$canonicalPath = $campaignSelf;
include __DIR__ . '/header.php';
?>
<main id="contenido">
<section class="campaign-hero"><div class="container campaign-split">
 <div><span class="eyebrow"><?= $escape($service[0]) ?> · SOTO DIGITAL</span><h1><?= $escape($service[1]) ?></h1><p class="campaign-lead"><?= $escape($service[2]) ?></p><a class="btn" href="<?= $escape($campaignSelf) ?>#propuesta">Pedir mini-auditoría gratuita</a><p class="campaign-micro">Desde Cantabria. Contigo, estés donde estés.</p>
 <?php if (in_array($slug,['google-ads','sem'],true)): ?><div class="campaign-ad"><small>Ejemplo ilustrativo · No es un resultado real de Google</small><h2><?= $escape($campaignVariant['example'] ?? 'Tu servicio, ante quien lo busca.') ?></h2><p>Un anuncio claro y una página que facilita pedir información.</p></div><?php endif; ?>
 </div>
 <div id="propuesta"><?php include __DIR__ . '/campaign-form.php'; ?></div>
</div></section>
<div class="campaign-strip"><div class="container">Tu objetivo marca el plan. <span>Tu negocio · Tus clientes · Los canales adecuados</span></div></div>
<?php $video = $content['videos'][$slug] ?? ''; if ($video && preg_match('~\Aimg/videos/[a-zA-Z0-9_-]+\.mp4\z~', $video) && is_file(__DIR__ . '/../' . $video)): ?>
<section><div class="container campaign-split"><div><span class="section-subtitle">TE LO CONTAMOS NOSOTROS</span><h2>Las preguntas que importan.<br>Una conversación con el equipo.</h2><p>Conoce cómo abordamos <?= $escape(mb_strtolower($service[0])) ?> y qué necesitamos saber de tu negocio.</p></div><video controls preload="none" aria-label="Entrevista al equipo de Soto Digital sobre <?= $escape($service[0]) ?>"><source src="/<?= $escape($video) ?>" type="video/mp4"><track kind="captions" src="/<?= $escape(substr($video, 0, -4)) ?>.vtt" srclang="es" label="Español" default></video></div></section>
<?php endif; ?>
<section id="plan"><div class="container"><div class="section-heading"><div><span class="section-subtitle">DE LA PRIMERA CONVERSACIÓN AL PLAN</span><h2>Antes de hacer,<br>entendemos tu negocio.</h2></div><p>Te explicamos qué proponemos, por qué y cómo valoraremos su evolución. Empezamos con una mini-auditoría inicial gratuita, sin compromiso de contratación. Acordamos contigo su alcance.</p></div><div class="process-grid campaign-process">
<article><span>01 / AUDITORÍA</span><h3>Tu punto de partida.</h3><p>Revisamos tu presencia digital, tu oferta y las oportunidades que encajan con el público al que quieres llegar. Acordamos el alcance de la revisión.</p></article>
<article><span>02 / ESTRATEGIA</span><h3>Un camino concreto.</h3><p>Elegimos contigo por dónde empezar, qué contar y en qué canales. Recibes un plan ajustado a tus objetivos y presupuesto.</p></article>
<article><span>03 / PROPUESTA</span><h3>Sabrás qué vamos a hacer.</h3><p>Te explicamos las acciones propuestas, la inversión y cómo revisaremos el avance. Acordamos contigo qué necesitamos para empezar, sin prometer ventas ni posiciones garantizadas.</p></article>
</div></div></section>
<section class="campaign-multichannel"><div class="container campaign-split"><div><span class="section-subtitle">UNA ESTRATEGIA MULTICANAL</span><h2>Cada canal cuenta.<br>Juntos, tienen sentido.</h2></div><div><p>Un anuncio despierta el interés. Una web explica tu valor. El contenido resuelve dudas y el email mantiene la conversación.</p><p>Cuando conviene a tu objetivo, te recomendamos combinar varios canales para acompañar al cliente en ese recorrido. Priorizamos los que aportan valor según tu presupuesto y tu momento.</p><div class="campaign-channels"><span>Google Ads</span><span>Web + SEO</span><span>Redes + Meta Ads</span><span>Contenido + Email</span><span>Pantalla LED</span></div></div></div></section>
<?php $cases = array_filter($content['cases'], static function ($case) use ($slug) { return in_array($slug, $case['services'], true); }); if ($cases): ?>
<section><div class="container"><span class="section-subtitle">SITUACIONES EN LAS QUE PUEDES RECONOCERTE</span><h2>Del reto a lo que<br>hicimos realidad.</h2><div class="campaign-case-grid"><?php foreach ($cases as $case): ?><article class="campaign-case"><span><?= $escape($case['sector']) ?></span><h3><?= $escape($case['situation']) ?></h3><p><?= $escape($case['work']) ?></p><strong>Qué entregamos</strong><p><?= $escape($case['result']) ?></p></article><?php endforeach; ?></div></div></section>
<?php endif; ?>
<?php if ($slug === 'estrategia-digital'): ?>
<section class="bg-soft"><div class="container"><span class="section-subtitle">UN PLAN, DISTINTAS SOLUCIONES</span><h2>Empezamos por lo que necesitas.</h2><nav class="campaign-service-links" aria-label="Servicios para tu estrategia"><?php foreach ($services as $key => $item): if ($key === $slug) continue; ?><a href="/campanas/<?= $escape($key) ?>.php"><?= $escape($item[0]) ?> <span>↗</span></a><?php endforeach; ?></nav></div></section>
<?php endif; ?>
<?php $testimonials = array_filter($content['testimonials'] ?? [], static function ($item) use ($slug) { return ($item['approved'] ?? false) === true && in_array($slug, $item['services'] ?? [], true); }); if ($testimonials): ?>
<section><div class="container"><span class="section-subtitle">EN PALABRAS DE NUESTROS CLIENTES</span><h2>Su experiencia,<br>contada por ellos.</h2><div class="campaign-case-grid"><?php foreach ($testimonials as $item): ?><figure class="campaign-case"><blockquote><p>«<?= $escape($item['quote']) ?>»</p></blockquote><figcaption><?= $escape($item['author']) ?> · <?= $escape($item['company']) ?></figcaption></figure><?php endforeach; ?></div></div></section>
<?php endif; ?>
<?php include __DIR__.'/team-section.php'; ?>
<section class="faq-section"><div class="container faq-layout"><div><span class="section-subtitle">ANTES DE EMPEZAR</span><h2>Las preguntas<br>que importan.</h2></div><div class="faq-list">
<details><summary>¿La mini-auditoría es gratuita?<span aria-hidden="true">+</span></summary><p>Sí. La revisión inicial es gratuita y no obliga a contratar. Tras conocer tu situación, acordamos qué revisaremos y cómo compartir contigo las primeras recomendaciones.</p></details>
<details><summary>¿Necesito tener una web?<span aria-hidden="true">+</span></summary><p>No. Puedes dejar el campo de web vacío y contarnos qué necesita tu negocio. Valoraremos el punto de partida antes de recomendar canales.</p></details>
<details><summary>¿Cuánto cuesta el servicio?<span aria-hidden="true">+</span></summary><p>Preparamos un presupuesto según el alcance y tus objetivos. La inversión que indiques en el cuestionario es orientativa, no una tarifa. En publicidad distinguimos los honorarios de la inversión en anuncios.</p></details>
<details><summary>¿Podéis garantizar resultados?<span aria-hidden="true">+</span></summary><p>No garantizamos ventas ni posiciones. Planteamos escenarios con supuestos claros y revisamos los resultados para ajustar el trabajo.</p></details>
</div></div></section>
<section class="campaign-contact"><div class="container"><span class="section-subtitle">EL SIGUIENTE PASO EMPIEZA AQUÍ</span><h2>Tu negocio, con un plan.</h2><p>Cuéntanos qué necesitas y revisamos contigo por dónde empezar.</p><a class="btn" href="<?= $escape($campaignSelf) ?>#propuesta">Pedir mini-auditoría gratuita</a></div></section>
<?php $approvedClients=array_filter($content['featured_clients'], static function($client){return ($client['approved']??false)===true;}); if ($slug === 'estrategia-digital' && $approvedClients): ?><section><div class="container"><span class="section-subtitle">CLIENTES CON LOS QUE AVANZAMOS</span><h2>Clientes con proyectos que puedes conocer.</h2><div class="campaign-channels"><?php foreach ($approvedClients as $client): ?><article class="campaign-client"><h3><?php if (!empty($client['project']) && preg_match('/^proyecto-[a-z-]+\.php$/',$client['project'])): ?><a href="<?= $escape($client['project']) ?>"><?= $escape($client['name']) ?></a><?php else: ?><?= $escape($client['name']) ?><?php endif; ?></h3><p><?= $escape($client['evidence']) ?></p></article><?php endforeach; ?></div></div></section><?php endif; ?>
</main>
<a class="campaign-sticky" data-form-sticky href="<?= $escape($campaignSelf) ?>#propuesta" hidden>Pedir mini-auditoría gratuita</a>
<?php include __DIR__ . '/footer.php'; ?>
