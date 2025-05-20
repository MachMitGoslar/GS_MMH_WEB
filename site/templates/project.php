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
  <section class="grid content">
    <div class="grid-item" data-span="1/1">
    <h1 class="font-titleXXL "><?=$page->headline()->isEmpty() ? $page->title() : $page->headline() ?></h1>
    <h2 class="font-titleXL font-weight-light"><?=$page->subheadline()?></h2>
    </div>

    <div id="project_description" class="grid-item" data-span="<?= $page->project_steps()->isNotEmpty() ? '2/3' : '1/1' ?>">
        <h3 class="font-headline"> Projektbeschreibung</h3>
        <div class="designer">
        <?php foreach ($page->text()->toLayouts() as $layout): ?>
          <div class="grid content">

          <?php foreach($layout->columns() as $column): ?>
            <div class="grid-item" data-span="<?=$column->width()?>">

            <?php foreach ($column->blocks() as $block): ?>
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
    <?php if($page->project_steps()->isNotEmpty()): ?>
    <div id="timeline" class="grid-item" data-span="1/3">
        <?php snippet(name: "components/project/projectTimeline", data: ['project_steps' => $page->project_steps()]) ?>
    </div>
    <?php endif ?>

  </section>
  <section>
  </section>
<?php snippet('general/footer'); ?>
<?php snippet('general/foot'); ?>
