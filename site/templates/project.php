<?php
/*
  Templates render the content of your pages.

  They contain the markup together with some control structures
  like loops or if-statements. The `$page` variable always
  refers to the currently active page.

  To fetch the content from each field we call the field name as a
  method on the `$page` object, e.g. `$page->title()`.

  This example template makes use of the `$gallery` variable defined
  in the `album.php` controller (/site/controllers/album.php)

  Snippets like the header and footer contain markup used in
  multiple templates. They also help to keep templates clean.

  More about templates: https://getkirby.com/docs/guide/templates/basics
*/
?>
<?php snippet('header') ?>
<article>
  <?php snippet('intro') ?>
  <?php 
    if ($page->project_status()->isNotEmpty()) {
      snippet('project_status', ['project_status' => $page->project_status(), 'showTitle' => true]);
    }

    if($page->team()->inNotEmpty()) {
      $ids = $page->team()->split();
      $members = $site->find("team")->children()->find($ids);
      snippet('team_images', ['team' => $members]);
    }
  ?>
  
  <div class="grid grid-cols-12">

    <div class="col-span-12 lg:col-span-4 gap-4">
      <div class="text">
        <?= $page->text() ?>
      </div>
    </div>

    <div class="col-span-12 lg:col-span-8">
      <ul class="grid grid-cols-2 gap-4">
        <?php foreach ($gallery as $image): ?>
        <li>
          <a href="<?= $image->url() ?>" data-lightbox>
            <figure class="img" style="--w:<?= $image->width() ?>;--h:<?= $image->height() ?>">
              <img src="<?= $image->resize(800)->url() ?>" alt="<?= $image->alt()->esc() ?>">
            </figure>
          </a>
        </li>
        <?php endforeach ?>
      </ul>
    </div>
        </div>
    <?php if($page->children()->isNotEmpty()): ?>
      <div class="project_steps">
    <h1 class=" font-black text-2xl"> Projektschritte </h1>

    <ol class="relative border-s border-gray-200 dark:border-gray-700">
        <?php foreach($page->children() as $entry): ?>
          <?php snippet('timeline_entry', ['entry' => $entry]) ?>
        <?php endforeach ?>
    </ol>
        </div>
    <?php endif ?>

</article>
<?php snippet('footer') ?>
