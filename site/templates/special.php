<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main class="main">

    <div class="mb-4">
        <?=snippet('sections/hero')?>
    </div>

    <section class="grid content mb-7">
      <?= $page->content_data() ?>
    </section>
</main>
<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
