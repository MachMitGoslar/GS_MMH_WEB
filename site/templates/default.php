<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php snippet('mainLayout', slots: true) ?>
  <?php slot()?>
  <div class="mb-4">
    <?=snippet('components/hero')?>
  </div>
  <section class="grid content">
    <h1 class="font-titleXXL grid-item" data-span="full"><?=$page->title()?></h1>
    <?php foreach ($page->layout()->toLayouts() as $layout): ?>
      <?php foreach($layout->columns() as $column): ?>
        <div class="grid-item" data-span="full">
            <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
              <?= $block ?>
            </div>
        </div>
      <?php endforeach ?>
    <?php endforeach ?>
  </section>

  <?php endslot() ?>
<?php endsnippet() ?>