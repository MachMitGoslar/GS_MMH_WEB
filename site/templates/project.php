<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php $teamMembers = $page->team()->toPages(); ?>
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>
  <main>
  <div class="mb-4">
    <?=snippet('sections/hero')?>
  </div>
  <?php if ($teamMembers->isNotEmpty()) : ?>
    <section class="project-team-strip-wrap">
      <div class="project-team-strip" aria-label="Projektteam">
        <?php foreach ($teamMembers as $member) : ?>
          <a href="<?= $member->url() ?>" class="project-team-member" title="<?= $member->title()->html() ?>">
            <?php if ($memberImage = $member->cover()) : ?>
              <img src="<?= $memberImage->crop(72, 72)->url() ?>" alt="<?= $member->title()->html() ?>" width="72" height="72">
            <?php else : ?>
              <span class="project-team-placeholder"><?= strtoupper(substr($member->title()->value(), 0, 1)) ?></span>
            <?php endif ?>
          </a>
        <?php endforeach ?>
      </div>
    </section>
  <?php endif ?>
  <section class="grid content">
    <div class="grid-item" data-span="1/1">
    <h1 class="font-titleXXL "><?=$page->headline()->isEmpty() ? $page->title() : $page->headline() ?></h1>
    <h2 class="font-titleXL font-weight-light"><?=$page->subheadline()?></h2>
    </div>

    <div id="project-description" class="grid-item" data-span="<?= $page->project_steps()->isNotEmpty() ? '2/3' : '1/1' ?>">
        <h3 class="font-headline"> Projektbeschreibung</h3>
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
        <section>
            <?php snippet('dreamform/forms', ['page' => $page]) ?>
        </section>
    </div>



      <?php if ($page->project_steps()->isNotEmpty()) : ?>
    <div id="timeline" class="grid-item" data-span="1/3">
            <?php snippet(name: 'content-types/projects/projectTimeline', data: ['project_steps' => $page->project_steps()]) ?>
    </div>
      <?php endif ?>

  </section>
  <section>
  </section>
<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
