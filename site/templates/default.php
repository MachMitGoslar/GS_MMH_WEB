<?php
$blockIsVisible = require kirby()->root('controllers') . '/blocks.php';
?><?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */

// Eingeloggter User (Panel)
$user = kirby()->user();

// Zeitprüfung (deutsche Zeit)
$timezone = new DateTimeZone(kirby()->option('date.timezone', 'Europe/Berlin'));
$now = (new DateTimeImmutable('now', $timezone))->getTimestamp();

$publish = null;
if ($page->publish_date()->isNotEmpty()) {
    $publishValue = $page->publish_date()->toDate('Y-m-d H:i');
    $publishDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', $publishValue, $timezone);
    $publish = $publishDate ? $publishDate->getTimestamp() : null;
}

$end = null;
if ($page->end_date()->isNotEmpty()) {
    $endValue = $page->end_date()->toDate('Y-m-d H:i');
    $endDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', $endValue, $timezone);
    $end = $endDate ? $endDate->getTimestamp() : null;
}

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
                                <?php if ($blockIsVisible($block)) : ?>
                                    <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
                                        <?= $block ?>
                                    </div>
                                <?php endif ?>

                            <?php endforeach ?>

                        </div>
                    <?php endforeach ?>

                </div>
            <?php endforeach ?>

        </section>
    </main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
