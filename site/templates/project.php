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

<?php snippet('project_status_bar', slots: true) ?>
  <?php slot('project_status') ?>
    <?php
    if ($project_status = $page->project_status()) {
      snippet('project_status', ['project_status' => $project_status, "size" => '2xl']);
    }
    ?>
  <?php endslot() ?>

  <?php if ($page->team()->exists() && ($team = $page->team()->toPages())): ?>
    <?php slot('team') ?>
    <?= snippet('team_images', ['team' => $team, 'showTitle' => true]) ?>
    <?php endslot() ?>
  <?php endif ?>
<?php endsnippet() ?>

<div class="grid grid-cols-3">
  <div class="col-span-3 lg:col-span-2 gap-4">
    <div class="text format">
      <?= $page->text() ?>
    </div>
    <?php if ($page->children()->isNotEmpty()): ?>
      <div class="project_steps mt-3">
        <h1 class=" font-black text-2xl"> Was bisher geschah</h1>
        <div class="steps p-3">
          <ol class="relative border-s border-gray-200 dark:border-gray-700">
            <?php foreach ($page->children() as $entry): ?>
              <?php snippet('timeline_entry', ['entry' => $entry]) ?>
            <?php endforeach ?>
          </ol>
        </div>

      </div>
    <?php endif ?>
  </div>

  <div class="col-span-3 lg:col-span-1">
    <ul class="grid grid-cols-2 gap-4">
      <?php foreach ($gallery as $image): ?>
        <li>
          <a href="<?= $image->url() ?>" data-lightbox>
            <figure class="img" style="--w:<?= $image->width() ?>;--h:<?= $image->height() ?>">
              <img src="<?= $image->crop(400, 400)->url() ?>" alt="<?= $image->alt()->esc() ?>">
            </figure>
          </a>
        </li>
      <?php endforeach ?>
    </ul>
  </div>
</div>

<?php endslot() ?>

<?php endsnippet() ?>