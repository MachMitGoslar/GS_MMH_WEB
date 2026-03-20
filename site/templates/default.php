<?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */

// Eingeloggter User (Panel)
$user = kirby()->user();

// Zeitprüfung
$now = time();
$publish = $page->publish_date()->isNotEmpty() ? $page->publish_date()->toDate() : null;
$end = $page->end_date()->isNotEmpty() ? $page->end_date()->toDate() : null;

// Nur blockieren, wenn KEIN User eingeloggt ist
if (!$user && (($publish && $publish > $now) || ($end && $end < $now))) {
    go(site()->errorPage()->url(), 404);
}
?>

<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

    <main>
        <div class="mb-4">
            <?= snippet('sections/hero') ?>
        </div>

        <section>
            <div class="grid content">
                <h1 class="font-titleXXL grid-item" data-span="1/1">
                    <?= $page->title() ?>
                </h1>
            </div>

            <?php foreach ($page->layout()->toLayouts() as $layout) : ?>
                <div class="grid content">

                    <?php foreach ($layout->columns() as $column) : ?>
                        <div class="grid-item" data-span="<?= $column->width() ?>">

                            <?php foreach ($column->blocks() as $block) : ?>
                                <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
                                    <?= $block ?>
                                </div>
                            <?php endforeach ?>

                        </div>
                    <?php endforeach ?>

                </div>
            <?php endforeach ?>

        </section>
    </main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>