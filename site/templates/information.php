<?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>
<main>
    <div class="mb-4">
        <?=snippet('sections/hero')?>
    </div>
    <section class="grid content">
        <div class="grid-item" data-span="1/1">
            <h1 class="font-titleXXL "><?=$page->headline()->isEmpty() ? $page->title() : $page->headline() ?></h1>
            <h2 class="font-titleXL font-weight-light"><?=$page->subheadline()?></h2>
        </div>

        <div id="project_description" class="grid-item" data-span="1/1">
            <div class="designer">
                <?php foreach ($page->text()->toLayouts() as $layout) : ?>
                    <div class="grid content">

                        <?php foreach ($layout->columns() as $column) : ?>
                            <div class="grid-item" data-span="<?=$column->width()?>">

                                <?php foreach ($column->blocks() as $block) : ?>
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
    <section>
    </section>
    <?php snippet('layout/footer'); ?>
    <?php snippet('layout/foot'); ?>
