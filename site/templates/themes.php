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
        <h1 class="font-titleXXL grid-item" data-span="1/1">
            <?= $page->headline()->or($page->title()) ?>
        </h1>

        <?php if ($page->subheadline()->isNotEmpty()) : ?>
            <p class="grid-item font-copyL" data-span="1/1">
                <?= $page->subheadline() ?>
            </p>
        <?php endif; ?>
    </section>

    <section class="grid content mb-7">
        <section class="grid-item" data-span="1/1">
            <ul class="grid mb-4">
                <?php foreach ($page->children()->listed() as $theme) : ?>
                    <?php snippet('content-types/themes/themeTeaserCard', ['theme' => $theme]) ?>
                <?php endforeach ?>
            </ul>
        </section>
    </section>

</main>

<?php snippet('layout/footer'); ?>
