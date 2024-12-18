<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php snippet('mainLayout', slots: true) ?>
  <?php slot()?>
  <section class="grid content">
    <h1 class="font-titleXXL grid-item-full-span"><?=$page->title()?></h1>
    <?php foreach($page->children()->listed() as $child): ?>
      <a class="grid-item-span4 color-fg-brand-primary" href="<?=$child->url()?>"><?=$child->title()?></a>
    <?php endforeach ?>
  </section>
  <?php endslot() ?>
<?php endsnippet() ?>