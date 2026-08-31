<?php
/**
 * One Themenfeld: intro text plus every project assigned to it.
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
$contentIsVisible = require kirby()->root('controllers') . '/blocks.php';
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
        <h1 class="font-titleXXL grid-item" data-span="1/1">
            <?= $page->headline()->or($page->title()) ?>
        </h1>

        <?php if ($page->subheadline()->isNotEmpty()) : ?>
            <p class="grid-item font-copyL" data-span="1/1">
                <?= $page->subheadline() ?>
            </p>
        <?php endif; ?>
    </section>

    <?php if ($page->text()->isNotEmpty()) : ?>
        <section class="grid content mb-6">
            <div class="grid-item" data-span="1/1">
                <div class="designer">
                    <?php foreach ($page->text()->toLayouts() as $layout) : ?>
                        <?php if (!$contentIsVisible($layout)) {
                            continue;
                        } ?>
                        <div class="grid project-layout-grid">
                            <?php foreach ($layout->columns() as $column) : ?>
                                <div class="grid-item" data-span="<?= $column->width() ?>">
                                    <?php foreach ($column->blocks() as $block) : ?>
                                        <?php if (!$contentIsVisible($block)) {
                                            continue;
                                        } ?>
                                        <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
                                            <?= $block ?>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </section>
    <?php endif ?>

    <section class="grid content mb-7">
        <section class="grid-item projects-listing" data-span="1/1">

            <?php if ($activeProjects->count()) : ?>
                <h2 class="font-title2 section-title">Laufende Projekte</h2>
                <ul class="grid mb-6">
                    <?php foreach ($activeProjects as $project) : ?>
                        <?php snippet('content-types/projects/projectTeaserCard', compact('project')) ?>
                    <?php endforeach ?>
                </ul>
            <?php endif ?>

            <?php if ($archivedProjects->count()) : ?>
                <h2 class="font-title2 section-title">Abgeschlossene Projekte</h2>
                <ul class="grid mb-4">
                    <?php foreach ($archivedProjects as $project) : ?>
                        <?php snippet('content-types/projects/projectTeaserCard', compact('project')) ?>
                    <?php endforeach ?>
                </ul>
            <?php endif ?>

            <?php if (!$activeProjects->count() && !$archivedProjects->count()) : ?>
                <p class="font-body">Diesem Themenfeld sind noch keine Projekte zugeordnet.</p>
            <?php endif ?>

        </section>
    </section>

</main>

<?php snippet('layout/footer'); ?>
