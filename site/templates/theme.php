<?php
/**
 * One Themenfeld: intro text plus every project assigned to it.
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
$activeProjects = $page->activeProjects();
$archivedProjects = $page->archivedProjects();
?>

<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main>

    <div class="mb-4">
        <?= snippet('sections/hero') ?>
    </div>

    <section class="grid content mb-5">
        <div class="grid-item" data-span="1/1">
            <?php snippet('utilities/page-title', [
                'title' => $page->headline()->or($page->title()),
                'subtitle' => $page->subheadline()->isNotEmpty() ? $page->subheadline() : null,
            ]) ?>
        </div>
    </section>

    <?php if ($page->text()->isNotEmpty()) : ?>
        <section class="grid content mb-6">
            <div class="grid-item" data-span="1/1">
                <div class="designer">
                    <?php snippet('utilities/layouts', ['layouts' => $page->text()->toLayouts(), 'wrapperClass' => 'grid project-layout-grid']) ?>
                </div>
            </div>
        </section>
    <?php endif ?>

    <section class="grid content mb-7">
        <section class="grid-item projects-listing" data-span="1/1">

            <?php if ($activeProjects->count()) : ?>
                <h2 class="font-title2 section-title">Laufende Projekte</h2>
                <div class="mb-6">
                    <?php snippet('utilities/card-list', ['pages' => $activeProjects]) ?>
                </div>
            <?php endif ?>

            <?php if ($archivedProjects->count()) : ?>
                <h2 class="font-title2 section-title">Abgeschlossene Projekte</h2>
                <div class="mb-4">
                    <?php snippet('utilities/card-list', ['pages' => $archivedProjects]) ?>
                </div>
            <?php endif ?>

            <?php if (!$activeProjects->count() && !$archivedProjects->count()) : ?>
                <p class="font-body">Diesem Themenfeld sind noch keine Projekte zugeordnet.</p>
            <?php endif ?>

        </section>
    </section>

</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
