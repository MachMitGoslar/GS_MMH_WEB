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
<?php snippet('main_layout', slots:true) ?>

  <?php slot('hero') ?>
    <?php snippet('hero', [
      'title' => $page->title(),
      'subheading' => $page->subheadline(),
      'cover' => $page->cover()
      ]) 
    ?>
  <?php endslot() ?>

  <?php slot() ?>

  <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-10">
  <?php foreach ($notes as $note): ?>
  <li class="pb-3 sm:pb-4" style="">
      <?php snippet('note', ['note' => $note]) ?>

  </li>
  <?php endforeach ?>
</ul>

  <?php endslot() ?> 

<?php endsnippet() ?>