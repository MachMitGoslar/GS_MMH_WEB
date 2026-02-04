<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php snippet('mainLayout', slots: true) ?>
  <?php slot()?>

  <div class="mb-4">
    <?=snippet('sections/hero', ['url' => "https://machmit.goslar.de/fileadmin/_processed_/a/0/csm_mmh___zukunft_nds_18d4453785.jpg"])?>
  </div>
  <div class="grid content mt-3 mb-6">
    <article class="grid-item-half-span-center">
      <div class="c-blog-heading">
        <h1  class="font-titleXXL"><?=$page->headline()?></h1>
      </div>

      <?php foreach ($page->blog()->toBlocks() as $block) : ?>
      <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
            <?= $block ?>
      </div>
      <?php endforeach ?>
    </article>
  </div>
  <?php endslot() ?>
<?php endsnippet() ?>