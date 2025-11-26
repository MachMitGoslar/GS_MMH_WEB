<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php snippet('general/head'); ?>
<?php snippet('general/header'); ?>

<main class="main">

    <div class="mb-4">
        <?=snippet('components/hero')?>
    </div>

    <section class="grid content mb-7">
      <?= $page->content_data() ?>
    </section>
</main>
<?php snippet('general/footer'); ?>
<?php snippet('general/foot'); ?>
