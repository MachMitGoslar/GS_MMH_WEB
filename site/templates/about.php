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
    <div class="grid-item" data-span="1/1">
        <?php snippet('utilities/page-title', [
            'title' => $page->headline()->or($page->title()),
            'subtitle' => $page->subheadline()->isNotEmpty() ? $page->subheadline() : null,
        ]) ?>
    </div>

    </div>
    <?php snippet('utilities/layouts', ['layouts' => $page->layout()->toLayouts()]) ?>
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
