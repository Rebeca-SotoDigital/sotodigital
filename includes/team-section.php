<?php
$teamContent = $content ?? require __DIR__.'/campaign-content.php';
$teamMembers = array_filter($teamContent['team'] ?? [], static function($member){return ($member['approved']??false)===true && !empty($member['name']) && !empty($member['description']);});
if ($teamMembers):
$teamEscape=static function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
?>
<section class="team-section"><div class="container"><h2>Las personas detrás de tu proyecto</h2><div class="team-grid">
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
