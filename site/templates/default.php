<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php snippet('general/head'); ?>
<?php snippet('general/header'); ?>
  <main>
  <div class="mb-4">
    <?=snippet('components/hero')?>
  </div>
  <section class="">
    <div class="grid content">
    <h1 class="font-titleXXL grid-item" data-span="1/1"><?=$page->title()?></h1>

    </div>
    <?php foreach ($page->layout()->toLayouts() as $layout) : ?>
      <div class="grid content">

        <?php foreach ($layout->columns() as $column) : ?>
            <?php foreach ($column->blocks() as $block) : ?>
        <div class="grid-item" data-span="<?=$column->width()?>">
            <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
                <?= $block ?>
            </div>
        </div>
            <?php endforeach ?>

        <?php endforeach ?>
      </div>

    <?php endforeach ?>
  </section>

  </main>
<?php snippet('general/footer'); ?>
<?php snippet('general/foot'); ?>
