<?php foreach ($editorialBlocks as $editorialBlock): ?>
<section class="bg-soft"><div class="container">
 <div class="section-heading"><div><span class="section-subtitle">DEL PLAN A LA PRÁCTICA</span><h2><?= $escape($editorialBlock['heading']) ?></h2></div><p><?= $escape($editorialBlock['intro']) ?></p></div>
 <div class="process-grid"><?php foreach ($editorialBlock['items'] as $editorialItem): ?><article><h3><?= $escape($editorialItem['title']) ?></h3><p><?= $escape($editorialItem['text']) ?></p></article><?php endforeach; ?></div>
</div></section>
<?php endforeach; ?>
