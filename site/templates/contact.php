<?php
/**
 * Contact page
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>
<?php snippet('layout/head', slots: true); ?>
<?php $blockIsVisible = require kirby()->root('controllers') . '/blocks.php'; ?>

<?php endsnippet() ?>
<?php snippet('layout/header'); ?>

<main>
    <div class="mb-4">
        <?= snippet('sections/hero') ?>
    </div>

    <!-- Title & Intro -->
    <section class="content mb-7">
        <h1 class="font-titleXXL mb-4">
            <?= $page->title() ?>
        </h1>

        <?php if ($page->intro()->isNotEmpty()) : ?>
            <div class="mb-6">
                <?= $page->intro()->kt() ?>
            </div>
        <?php endif ?>
    </section>

    <!-- Contact + Map -->
    <?= snippet('sections/contact-map', [
            'title' => 'Ihr Kontakt zu uns',
            'email' => $page->email(),
            'website' => $page->website(),
            'phone' => $page->phone(),
            'social' => $page->social(),
            'addressLabel' => $page->address()->kt(),
            'lat' => $page->lat()->value(),
            'lng' => $page->lng()->value(),
            'mapboxToken' => $page->mapbox_token(),
    ]) ?>



    <!-- 🔽 Flexible Blocks -->
    <section class="content">
        <div class="designer">
            <?php foreach ($page->text()->toBlocks() as $block) : ?>
                <?php if (!$blockIsVisible($block)) {
                    continue;
                } ?>
                <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
                    <?= $block ?>
                </div>
            <?php endforeach ?>
        </div>
    </section>
</main>

<?php snippet('layout/footer'); ?>
