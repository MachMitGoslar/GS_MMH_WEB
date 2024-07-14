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
      'cover' => $site->cover()
      ]) 
    ?>
  <?php endslot() ?>

  <?php slot() ?>
    <?php if ($projectsPage = page('projects')): ?>
      <div class="projects">
      <h1> Unsere Projekte </h1>
      
      <ul class="pt-10 grid md:grid-cols-3 grid-cols-2 gap-4 grid-flow-row-dense">
        <?php foreach ($projectsPage->children()->listed() as $album): ?>
          <li>
            <figure
              class="relative max-w-sm hover:border-gold hover:border-solid hover:border-2 rounded-lg hover:rounded-lg col-span-2 row-span-2">
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
                  <p><?= $album->cover_focus() ?></p>
                  <img
                    class="filter hover:blur aspect-square	border-2 border-transparent object-cover h-auto max-w-full rounded-lg hover:rounded-lg transition-all duration-300 cursor-pointer"
                    style="focus: <?= $cover->focus() ?>" src="<?= $cover->resize(1024, 1024)->url() ?>"
                    alt="<?= $cover->alt()->esc() ?>">

                <?php endif ?>
              </a>

              <figcaption
                class=" rounded-lg absolute text-lg  text-white text-center bottom-0 py-5 w-full bg-gradient-to-t from-gold">
                <p class="text-white font-black"><?= $album->title()->esc() ?></p>
              </figcaption>
            </figure>
          </li>

        <?php endforeach ?>
      </ul>
      </div>
      <?php snippet("oveda", ["oveda_search" => $page->oveda()]) ?>
    <?php endif ?>
  <?php endslot() ?> 

<?php endsnippet() ?>