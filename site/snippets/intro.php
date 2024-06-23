<?php
/*
  Snippets are a great way to store code snippets for reuse
  or to keep your templates clean.

  This intro snippet is reused in multiple templates.
  While it does not contain much code, it helps to keep your
  code DRY and thus facilitate maintenance when you have
  to make changes.

  More about snippets:
  https://getkirby.com/docs/guide/templates/snippets
*/
?>
<header class="">
  <?php if ($cover = $page->cover()): ?>
    <div class="relative">
      <img src="<?= $cover->crop(1200, 600)->url() ?>" alt="<?= $cover->alt()->esc() ?>">
      <div class="absolute bottom-10 left-0 backdrop-blur-sm px-4 py-2 bg-gray-400/70 rounded-e-md border-gold-600 border-b-2">
        <h1><?= $page->headline()->or($page->title())->esc() ?></h1>
        <?php if ($page->subheadline()->isNotEmpty()): ?>
          <h2><small><?= $page->subheadline()->esc() ?></small></h2>
        <?php endif ?>

  <?php else: ?>
    <h1><?= $page->headline()->or($page->title())->esc() ?></h1>
    <?php if ($page->subheadline()->isNotEmpty()): ?>
      <h2><small><?= $page->subheadline()->esc() ?></small></h2>
    <?php endif ?>
  <?php endif ?>
</header>
