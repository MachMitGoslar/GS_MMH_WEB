<?php
/**
 * Keys (Schlüssel) Listing Template
 * @var \Kirby\Cms\Page $page
 */
?>

<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main class="main">

    <!-- Hero -->
    <section class="rooms-hero">
        <?php if ($page->cover() && $cover = $page->cover()->toFile()) : ?>
            <div class="rooms-hero-image">
                <img src="<?= $cover->crop(1920, 600)->url() ?>"
                     alt="<?= $page->title()->html() ?>"
                     loading="eager">
                <div class="rooms-hero-overlay"></div>
            </div>
        <?php endif ?>
        <div class="rooms-hero-content">
            <div class="grid content">
                <div class="grid-item" data-span="1/1">
                    <h1 class="font-titleXXL"><?= $page->headline()->or($page->title())->html() ?></h1>
                    <?php if ($page->subheadline()->isNotEmpty()) : ?>
                        <p class="font-titleXL font-weight-light"><?= $page->subheadline()->html() ?></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Intro -->
    <?php if ($page->description()->isNotEmpty()) : ?>
        <section class="content">
            <?php foreach ($page->description()->toBlocks() as $block): ?>
                <?= $block ?>
            <?php endforeach ?>
        </section>
    <?php endif ?>



</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
