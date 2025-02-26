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
    <h1 class="font-titleXXL grid-item-full-span"><?=$page->title()?></h1>
  </section>
  <?php foreach ($page->blog()->toBlocks() as $block): ?>
      <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
        <?= $block ?>
      </div>
      <?php endforeach ?>
  <?php endslot() ?>
<?php endsnippet() ?>