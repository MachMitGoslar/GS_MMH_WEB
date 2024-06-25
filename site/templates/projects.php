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
      'title' => $page->headline(),
      'subheading' => $page->subheadline(),
      'cover' => $page->cover()
      ]) 
    ?>
  <?php endslot() ?>

  <?php slot() ?>
  <ul class="grid lg:grid-cols-4 gap-4 md:grid-cols-2 xs:grid-col-1">
  <?php foreach ($page->children()->listed() as $project): ?>
  <a href="<?= $project->url() ?>">
  <li class="col-span-1">


    <figure class="relative hover:border-gold hover:border-solid hover:border-2 rounded-lg hover:rounded-lg">

          <?php
          /*
            The `cover()` method defined in the `album.php`
            page model can be used everywhere across the site
            for this type of page

            We can automatically resize images to a useful
            size with Kirby's built-in image manipulation API
          */
          ?>
          <?php if ($cover = $project->cover()): ?>
            <p><?= $project->cover_focus() ?></p>
              <img class="filter hover:blur aspect-square	border-2 border-transparent object-cover h-auto max-w-full rounded-lg hover:rounded-lg transition-all duration-300 cursor-pointer" style="focus: <?= $cover->focus() ?>" src="<?= $cover->resize(1024, 1024)->url() ?>" alt="<?= $cover->alt()->esc() ?>">

          <?php endif ?>

          <figcaption class=" rounded-lg absolute text-lg  text-white text-center bottom-0 py-5 w-full bg-gradient-to-t from-gold">
              <p class="text-white font-black" ><?= $project->title()->esc() ?></p>
          </figcaption>
        </figure>
  </li>
  </a>

  <?php endforeach ?>
</ul>
  <?php endslot() ?> 

<?php endsnippet() ?>
