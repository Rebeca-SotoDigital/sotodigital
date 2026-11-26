<?php
require_once __DIR__.'/campaign-config.php';
$blogSettings = require __DIR__.'/blog-settings.php';
$blogPosts = require __DIR__.'/blog-posts.php';
$blogPost = null;
foreach ($blogPosts as $candidate) {
    if ($candidate['slug'] === ($blogSlug ?? '') && !empty($candidate['approved'])) { $blogPost = $candidate; break; }
}
if (!$blogPost) { http_response_code(404); require __DIR__.'/../404.php'; return; }
$blogEscape = static function ($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); };
$pageTitle = $blogPost['seo_title'];
$pageDescription = $blogPost['summary'];
$canonicalPath = '/blog/'.$blogPost['slug'].'.php';
$blogAuthorName = trim($blogSettings['author_name']);
$blogAuthorRole = trim($blogSettings['author_role']);
$blogAuthor = $blogAuthorName !== ''
    ? ['@type'=>$blogSettings['author_type'],'name'=>$blogAuthorName]
    : ['@type'=>'Organization','name'=>$blogSettings['publisher_name'],'url'=>'https://soto-digital.com/'];
if ($blogAuthorName !== '' && $blogAuthorRole !== '') $blogAuthor['jobTitle'] = $blogAuthorRole;
$blogModified = !empty($blogSettings['show_dates']) ? ($blogPost['modified_date'] ?? '') : '';
$blogPublished = !empty($blogSettings['show_dates']) ? ($blogPost['published_date'] ?? '') : '';
$blogDateLabel = static function ($date) {
    $months = [1=>'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed ? $parsed->format('j').' de '.$months[(int)$parsed->format('n')].' de '.$parsed->format('Y') : '';
};
$pageStructuredData = [
    '@context'=>'https://schema.org', '@type'=>'BlogPosting',
    'headline'=>$blogPost['title'], 'description'=>$pageDescription,
    'mainEntityOfPage'=>'https://soto-digital.com'.$canonicalPath,
    'author'=>$blogAuthor,
    'publisher'=>['@type'=>'Organization','name'=>'Soto Digital','url'=>'https://soto-digital.com/'],
    'inLanguage'=>'es',
];
if ($blogModified !== '') $pageStructuredData['dateModified'] = $blogModified;
if ($blogPublished !== '') $pageStructuredData['datePublished'] = $blogPublished;
include __DIR__.'/header.php';
?>
<main id="contenido" class="blog-page">
    <article class="container blog-article">
        <header class="blog-heading">
            <a class="blog-back" href="/blog/">← Volver al blog</a>
            <p class="section-subtitle"><?= $blogEscape($blogPost['category']) ?></p>
            <h1><?= $blogEscape($blogPost['title']) ?></h1>
            <p class="blog-byline">Por <?= $blogEscape($blogAuthorName !== '' ? $blogAuthorName : $blogSettings['publisher_name']) ?><?php if ($blogModified !== ''): ?> · Actualizado el <time datetime="<?= $blogEscape($blogModified) ?>"><?= $blogEscape($blogDateLabel($blogModified)) ?></time><?php endif; ?><?php if ($blogPublished !== ''): ?> · Publicado el <time datetime="<?= $blogEscape($blogPublished) ?>"><?= $blogEscape($blogDateLabel($blogPublished)) ?></time><?php endif; ?></p>
            <p class="blog-intro"><?= $blogEscape($blogPost['summary']) ?></p>
            <?php if (!empty($blogPost['takeaway'])): ?><aside class="blog-note blog-takeaway"><h2>En resumen</h2><p><?= $blogEscape($blogPost['takeaway']) ?></p></aside><?php endif; ?>
        </header>
        <nav class="blog-toc" aria-label="Índice del artículo"><h2>En este artículo</h2><ol>
            <?php foreach ($blogPost['sections'] as $section): ?><li><a href="#<?= $blogEscape($section['id']) ?>"><?= $blogEscape($section['title']) ?></a></li><?php endforeach; ?>
        </ol></nav>
        <div class="blog-copy">
            <?php foreach ($blogPost['sections'] as $section): ?>
            <section id="<?= $blogEscape($section['id']) ?>"><h2><?= $blogEscape($section['title']) ?></h2><?= $section['html'] /* Reviewed repository HTML, never request input. */ ?>
                <?php if (!empty($section['figure'])): $figure = $section['figure']; ?>
                    <?php if (!empty($figure['src']) && !empty($figure['alt']) && !empty($figure['caption'])): ?>
                    <figure class="blog-figure"><img src="<?= $blogEscape($figure['src']) ?>" alt="<?= $blogEscape($figure['alt']) ?>" width="1200" height="800" loading="lazy" decoding="async"><figcaption><?= $blogEscape($figure['caption']) ?></figcaption></figure>
                    <?php elseif (soto_show_pending()): ?>
                    <figure class="blog-figure blog-pending"><p>[PENDIENTE: <?= $blogEscape($figure['pending_kind'] ?? 'captura') ?>] (<?= $blogEscape($figure['pending']) ?>).</p><figcaption><?= !empty($figure['pending_kind']) ? 'Ilustración por aportar.' : 'Captura por aportar.' ?></figcaption></figure>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($section['example_paragraphs'])): ?><aside class="blog-note"><h3><?= $blogEscape($section['example_heading'] ?? 'Así lo revisamos en Soto Digital') ?></h3><?php foreach ($section['example_paragraphs'] as $paragraph): ?><p><?= $blogEscape($paragraph) ?></p><?php endforeach; ?></aside><?php endif; ?>
                <?php if (!empty($section['pending_example']) && soto_show_pending()): ?><aside class="blog-note blog-pending"><h3><?= $blogEscape($section['example_heading'] ?? 'Así lo revisamos en Soto Digital') ?></h3><p><?= $blogEscape($section['pending_example']) ?></p></aside><?php endif; ?>
            </section>
            <?php endforeach; ?>
            <?php if (!empty($blogPost['closing_html'])): ?><?= $blogPost['closing_html'] ?><?php endif; ?>
        </div>
        <aside class="blog-cta" aria-label="Propuesta para tu negocio"><h2>¿Lo vemos en tu negocio?</h2><p>Cuéntanos tu punto de partida. Revisaremos los datos y te responderemos en un máximo de <?= $blogEscape(campaign_response_time()) ?>.</p><a class="btn" href="/#diagnostico"><?= SOTO_PROPOSAL_CTA ?></a></aside>
        <nav class="blog-related" aria-label="Otros artículos"><h2>Para seguir leyendo</h2><ul>
            <?php foreach ($blogPosts as $related): if (empty($related['approved']) || $related['slug'] === $blogPost['slug']) continue; ?>
            <li><a href="/blog/<?= $blogEscape($related['slug']) ?>.php"><?= $blogEscape($related['title']) ?></a></li>
            <?php endforeach; ?>
        </ul></nav>
    </article>
</main>
<?php include __DIR__.'/footer.php'; ?>
