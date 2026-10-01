<?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */

$heroImages = $page->cover(); // check if cover() exists and then run the toFiles() method
?>


<div class="c-hero">

    <?php // instead of getting pictures from here, use the images of the field "hero" of each page?>
    <?php if ($heroImages && $heroImages->isNotEmpty()) : ?>
            <?php snippet('utilities/image', [
                'file' => $heroImages,
                'role' => 'hero',
                'ratio' => '2:1',
                'lazy' => false,
            ]) ?>

    <?php else : ?>
        <img src="<?= $url ?? 'https://picsum.photos/1600/800?' ?>" alt="Ein zufällig ausgewähltes Bild" width="1600" height="800">
    <?php endif; ?>
</div>