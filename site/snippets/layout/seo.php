<?php

use Kirby\Toolkit\Str;

$siteTitle = $site->title()->or('MachMit!Haus')->value();

$pageTitleField = $page?->content()->get('seo_title');
if ($pageTitleField?->isEmpty()) {
    $pageTitleField = $page?->content()->get('headline');
}
if ($pageTitleField?->isEmpty()) {
    $pageTitleField = $page?->title();
}

$pageTitle = trim((string) ($pageTitleField?->value() ?? ''));
$metaTitle = $pageTitle !== '' ? $pageTitle . ' | ' . $siteTitle : $siteTitle;

$descriptionFields = [
    'seo_description',
    'description',
    'subheadline',
    'wellcometext',
    'intro',
    'text',
];

$metaDescription = '';

if ($page) {
    foreach ($descriptionFields as $fieldName) {
        $field = $page->content()->get($fieldName);
        $value = trim(strip_tags((string) $field->kt()));

        if ($value !== '') {
            $metaDescription = $value;
            break;
        }
    }
}

if ($metaDescription === '') {
    $homeText = trim(strip_tags((string) site()->homePage()->content()->get('wellcometext')->kt()));
    $metaDescription = $homeText !== '' ? $homeText : 'MachMit!Haus Goslar';
}

$metaDescription = esc(Str::excerpt($metaDescription, 160, false));

$canonicalUrl = $page?->url() ?? $site->url();

$socialImage = null;
$socialImageField = $page?->content()->get('social_image');
if ($socialImageField?->isNotEmpty()) {
    $socialImage = $socialImageField->toFile();
}

if (!$socialImage && $page?->content()->get('cover')->isNotEmpty()) {
    $socialImage = $page->cover()->toFile();
}

$socialImageUrl = $socialImage?->url() ?? url('assets/svg/machmit-logo.svg');
$socialImageType = $socialImage?->mime() ?? 'image/svg+xml';
$robots = 'index,follow';
$locale = 'de_DE';
$twitterCard = $socialImage ? 'summary_large_image' : 'summary';
?>
<title><?= esc($metaTitle) ?></title>
<meta name="description" content="<?= $metaDescription ?>">
<meta name="robots" content="<?= $robots ?>">
<link rel="canonical" href="<?= esc($canonicalUrl) ?>">

<meta property="og:locale" content="<?= $locale ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= esc($siteTitle) ?>">
<meta property="og:title" content="<?= esc($metaTitle) ?>">
<meta property="og:description" content="<?= $metaDescription ?>">
<meta property="og:url" content="<?= esc($canonicalUrl) ?>">
<meta property="og:image" content="<?= esc($socialImageUrl) ?>">
<meta property="og:image:type" content="<?= esc($socialImageType) ?>">

<meta name="twitter:card" content="<?= $twitterCard ?>">
<meta name="twitter:title" content="<?= esc($metaTitle) ?>">
<meta name="twitter:description" content="<?= $metaDescription ?>">
<meta name="twitter:image" content="<?= esc($socialImageUrl) ?>">

<link rel="icon" type="image/svg+xml" href="<?= url('assets/svg/machmit-logo.svg') ?>">
<link rel="apple-touch-icon" href="<?= url('assets/svg/machmit-logo.svg') ?>">
