<?php
$teamContent = $content ?? require __DIR__.'/campaign-content.php';
$teamMembers = array_filter($teamContent['team'] ?? [], static function($member){return ($member['approved']??false)===true && !empty($member['name']) && !empty($member['description']);});
if ($teamMembers):
$teamEscape=static function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
?>
<section class="team-section"><div class="container"><h2>Las personas detrás de tu proyecto</h2><p>Hablarás con nosotros desde la primera conversación. Revisaremos tu negocio y te explicaremos qué proponemos y por qué.</p><p>En Soto Digital trabajamos en webs, redes y marcas tanto para clientes como para nuestros propios negocios. Esa perspectiva nos lleva a mirar más allá de una pieza: cómo se presenta la oferta, cómo se facilita el contacto y qué necesita la persona para decidir. Desde Cantabria, trabajamos contigo por videollamada estés donde estés en España.</p><?php if (!empty($showTeamPresentation) && is_file(__DIR__.'/../img/videos/presentacion-soto-digital.mp4')): ?>
<figure class="team-presentation">
 <h3>Conoce a Soto Digital</h3>
 <p>Rebeca te cuenta cómo podemos ayudarte y cómo trabajamos contigo.</p>
 <video controls muted playsinline preload="none" data-viewport-video width="720" height="1280" poster="/img/videos/presentacion-soto-digital-portada.jpg" aria-label="Presentación de Soto Digital por Rebeca">
  <source src="/img/videos/presentacion-soto-digital.mp4" type="video/mp4">
  Tu navegador no permite reproducir este vídeo. <a href="/img/videos/presentacion-soto-digital.mp4">Ver la presentación de Soto Digital</a>.
 </video>
 <figcaption>Presentación de Soto Digital · Rebeca, cofundadora.</figcaption>
</figure>
<?php endif; ?>
<div class="team-grid">
<?php foreach($teamMembers as $member): ?><article>
<?php if(!empty($member['photo']) && preg_match('~^img/equipo/[a-zA-Z0-9_.-]+$~',$member['photo']) && is_file(__DIR__.'/../'.$member['photo'])): ?><img src="/<?= $teamEscape($member['photo']) ?>" alt="<?= $teamEscape($member['name']) ?>" width="400" height="400" loading="lazy"><?php elseif (soto_show_pending()): ?><div class="team-photo-placeholder">[PENDIENTE: foto]</div><?php endif; ?>
<h3><?= $teamEscape($member['name']) ?></h3>
<p><strong><?= $teamEscape($member['description']) ?></strong></p>
<?php if (!empty($member['personal']) && (soto_show_pending() || !str_starts_with($member['personal'], '[PENDIENTE'))): ?>
    <p><?= $teamEscape($member['personal']) ?></p>
<?php endif; ?>
</article><?php endforeach; ?>
</div></div></section>
<?php endif; ?>
