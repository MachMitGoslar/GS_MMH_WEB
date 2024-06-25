<?php
/*
  Templates render the content of your pages.

  They contain the markup together with some control structures
  like loops or if-statements. The `$page` variable always
  refers to the currently active page.

  To fetch the content from each field we call the field name as a
  method on the `$page` object, e.g. `$page->title()`.

  This home template renders content from others pages, the children of
  the `photography` page to display a nice gallery grid.

  Snippets like the header and footer contain markup used in
  multiple templates. They also help to keep templates clean.

  More about templates: https://getkirby.com/docs/guide/templates/basics
*/

?>
<?php snippet('main_layout', slots: true) ?>

<?php slot('hero') ?>
<?php snippet('hero', [
  'title' => $page->headline(),
  'subheading' => $page->subheadline(),
  'cover' => $page->cover()
])
  ?>
<?php endslot() ?>

<?php slot() ?>
<?php foreach ($tags as $tag): ?>

  <h3> <?= $tag ?> </h3>
  <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 items-stretch xl:grid-cols-4">
    <?php foreach ($page->children()->filterBy('teams', $tag, ",") as $member): ?>
      <?= snippet('member_card', ['member' => $member, 'short' => true]) ?>
    <?php endforeach ?>
  </div>
<?php endforeach; ?>
<?php endslot() ?>

<?php endsnippet() ?>