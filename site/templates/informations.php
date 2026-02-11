<?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>

<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main>

    <!-- HERO -->
    <div class="mb-4">
        <?= snippet('sections/hero') ?>
    </div>

    <!-- CONTENT -->
    <section class="grid content mb-7">

        <!-- TITLE (immer sichtbar, wie im Beispiel) -->


        <!-- HEADER + SUBHEADER -->
        <section class="grid-item mb-4" data-span="1/1">
            <div class="grid-item" data-span="1/1">
                <h1 class="font-titleXXL "><?=$page->headline()->isEmpty() ? $page->title() : $page->headline() ?></h1>
                <h2 class="font-titleXL font-weight-light"><?=$page->subheadline()?></h2>
            </div>
        </section>

        <!-- TEXT / BLOCKS -->
        <section class="grid-item" data-span="1/1">
            <?= $page->text()->toBlocks() ?>
        </section>

    </section>

</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
