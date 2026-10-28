<?php

/**
 * Search engine and social embed meta tags.
 *
 * Every value is resolved by the SEO plugin so that the sitemap, the
 * structured data snippet and the panel preview stay in sync with what
 * is rendered here.
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page|null $page
 */

$meta = $page ? $page->seo() : $site->seo();
?>
<title><?= esc($meta->title()) ?></title>
<meta name="description" content="<?= esc($meta->description()) ?>">
<meta name="robots" content="<?= esc($meta->robots()) ?>">
<link rel="canonical" href="<?= esc($meta->canonical()) ?>">

<meta property="og:locale" content="<?= esc($meta->locale()) ?>">
<meta property="og:type" content="<?= esc($meta->type()) ?>">
<meta property="og:site_name" content="<?= esc($meta->siteTitle()) ?>">
<meta property="og:title" content="<?= esc($meta->title()) ?>">
<meta property="og:description" content="<?= esc($meta->description()) ?>">
<meta property="og:url" content="<?= esc($meta->canonical()) ?>">
<meta property="og:image" content="<?= esc($meta->imageUrl()) ?>">
<meta property="og:image:secure_url" content="<?= esc($meta->imageUrl()) ?>">
<meta property="og:image:type" content="<?= esc($meta->imageType()) ?>">
<meta property="og:image:width" content="<?= $meta::IMAGE_WIDTH ?>">
<meta property="og:image:height" content="<?= $meta::IMAGE_HEIGHT ?>">
<meta property="og:image:alt" content="<?= esc($meta->imageAlt()) ?>">
<?php if ($meta->isArticle()) : ?>
    <?php if ($published = $meta->publishedTime()) : ?>
<meta property="article:published_time" content="<?= esc($published) ?>">
    <?php endif; ?>
    <?php if ($modified = $meta->modifiedTime()) : ?>
<meta property="article:modified_time" content="<?= esc($modified) ?>">
    <?php endif; ?>
<?php endif; ?>

<meta name="twitter:card" content="<?= esc($meta->twitterCard()) ?>">
<meta name="twitter:title" content="<?= esc($meta->title()) ?>">
<meta name="twitter:description" content="<?= esc($meta->description()) ?>">
<meta name="twitter:image" content="<?= esc($meta->imageUrl()) ?>">
<meta name="twitter:image:alt" content="<?= esc($meta->imageAlt()) ?>">
<?php if ($twitterSite = $meta->twitterSite()) : ?>
<meta name="twitter:site" content="<?= esc($twitterSite) ?>">
<?php endif; ?>

<link rel="icon" type="image/svg+xml" href="<?= url('assets/svg/machmit-logo.svg') ?>">
<link rel="apple-touch-icon" href="<?= url('assets/pngs/og-default.png') ?>">
<?php snippet('seo/jsonld', ['meta' => $meta]) ?>
