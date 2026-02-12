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
  <section class="">
    <div class="grid content">
    <h1 class="font-titleXXL grid-item" data-span="1/1"><?=$page->title()?></h1>

    </div>
    <?php foreach ($page->layout()->toLayouts() as $layout) : ?>
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
  </section>

  <!-- Team Galleries Section -->
  <?php snippet('content-types/team/teamsSection', [
      'staff' => $staff,
      'volunteers' => $volunteers,
      'partners' => $partners,
      'issuers' => $issuers,
  ]) ?>

  </main>
<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
