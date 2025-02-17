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
    <h1 class="font-titleXXL grid-item-full-span"><?=$page->headline()?></h1>
    <h2 class="font-titleXL font-weight-light grid-item-full-span"><?=$page->subheadline()?></h2>

    <div id="project_description" class="grid-item-two-third-span">
        <h3 class="font-headline"> Projektbeschreibung</h3>
        <?=$page->text()->toBlocks()?>
    </div>
    <?php if($page->project_steps()->isNotEmpty()): ?>
    <div id="timeline" class="grid-item-one-third-span">
        <?php snippet(name: "components/project/projectTimeline", data: ['project_steps' => $page->project_steps()]) ?>
    </div>
    <?php endif ?>

  </section>
  <section>
  </section>
  <?php endslot() ?>
<?php endsnippet() ?>