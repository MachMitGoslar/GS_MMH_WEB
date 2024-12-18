<?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */

$heroImages = $page->cover()?->toFiles(); // check if cover() exists and then run the toFiles() method
?>


<div class="c-hero">

    <?php // instead of getting pictures from here, use the images of the field "hero" of each page ?>
    <?php if ($heroImages && $heroImages->isNotEmpty()): ?>
        <?php foreach ($heroImages as $image): ?>
            <img src="<?= $image->url() ?>" alt="<?= $image->alt() ?>">
        <?php endforeach; ?>

    <?php else: ?>
        <img src="<?= $url ?? 'https://picsum.photos/1600/800?' ?>" alt="Ein zufällig ausgewähltes Bild">
    <?php endif; ?>
</div>