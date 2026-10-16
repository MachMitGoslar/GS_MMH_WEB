<?php
/**
 * Overview of all Themenfelder.
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>

<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main>

    <div class="mb-4">
        <?= snippet('sections/hero') ?>
    </div>

    <section class="grid content mb-7">
        <div class="grid-item" data-span="1/1">
            <?php snippet('utilities/page-title', [
                'title' => $page->headline()->or($page->title()),
                'subtitle' => $page->subheadline()->isNotEmpty() ? $page->subheadline() : null,
            ]) ?>
        </div>
    </section>

    <section class="grid content mb-7">
        <section class="grid-item" data-span="1/1">
            <div class="mb-4">
                <?php snippet('utilities/card-list', ['pages' => $page->children()->listed()]) ?>
            </div>
        </section>
    </section>

</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
