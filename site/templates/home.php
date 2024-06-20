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
<?php snippet('header') ?>
  <?php snippet('intro') ?>
  <?php
  /*
    We always use an if-statement to check if a page exists to
    prevent errors in case the page was deleted or renamed before
    we call a method like `children()` in this case
  */
  ?>
  <?php if ($photographyPage = page('photography')): ?>
  <ul class="grid grid-cols-2 md:grid-cols-3 gap-4">
    <?php foreach ($photographyPage->children()->listed() as $album): ?>
    <li>
        <figure class="relative max-w-sm transition-all duration-300 cursor-pointer filter grayscale hover:grayscale-0">
        <a href="<?= $album->url() ?>">

          <?php
          /*
            The `cover()` method defined in the `album.php`
            page model can be used everywhere across the site
            for this type of page

            We can automatically resize images to a useful
            size with Kirby's built-in image manipulation API
          */
          ?>
          <?php if ($cover = $album->cover()): ?>
          <img class="h-auto max-w-full rounded-lg" src="<?= $cover->resize(1024, 1024)->url() ?>" alt="<?= $cover->alt()->esc() ?>">
          <?php endif ?>
          </a>

          <figcaption class="absolute px-4 text-lg text-white bottom-6">
              <p class="text-white"><?= $album->title()->esc() ?></p>
          </figcaption>
        </figure>
    </li>
    <?php endforeach ?>
  </ul>
  <?php endif ?>
<?php snippet('footer') ?>
