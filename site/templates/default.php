<?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>
<?php
if (!$page->isVisible()) {
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
                <div class="grid-item" data-span="1/1">
                    <?php snippet('utilities/page-title', [
                        'title' => $page->headline()->or($page->title()),
                        'subtitle' => $page->subheadline()->isNotEmpty() ? $page->subheadline() : null,
                    ]) ?>
                </div>
            </div>

            <?php snippet('utilities/layouts', ['layouts' => $page->layout()->toLayouts()]) ?>

        </section>
    </main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
