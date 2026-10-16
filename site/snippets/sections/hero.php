<?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 * @var bool $placeholder Show the brand placeholder when there is no image (default: random photo)
 * @var \Kirby\Cms\File|null $image Image to show instead of the page's cover (hero block)
 * @var string|null $variant wide (default) or standard (16:9)
 */

$placeholder ??= false;
$image ??= null;
$variant ??= 'wide';

$heroImages = $image ?? $page->cover(); // check if cover() exists and then run the toFiles() method
?>


<div class="c-hero" data-variant="<?= esc($variant) ?>">

    <?php // instead of getting pictures from here, use the images of the field "hero" of each page?>
    <?php if ($heroImages && $heroImages->isNotEmpty()) : ?>
            <?php snippet('utilities/image', [
                'file' => $heroImages,
                'role' => 'hero',
                'ratio' => '2:1',
                'lazy' => false,
            ]) ?>

    <?php elseif ($placeholder) : ?>
        <?php snippet('utilities/imagePlaceholder') ?>

    <?php else : ?>
        <img src="<?= $url ?? 'https://picsum.photos/1600/800?' ?>" alt="Ein zufällig ausgewähltes Bild" width="1600" height="800">
    <?php endif; ?>
</div>