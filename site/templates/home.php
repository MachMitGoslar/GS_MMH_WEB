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

    <!---- Greeter ---->
    <div class="content_element_half py-6">
      <h1 class="text-titleXXL mb-2"> <?=$site->greeter_headline() ?> </h1>
      <p class="text-body"> <?=$site->greeter_text()?></p>
    </div>
    <!---- Newsletter CTA ---->
    <div class="content_element_half bg-dead-pixel-1300 flex flex-col justify-between p-6">
    <div class="mb-6">
    <h2 class="text-title2 text-dead-pixel-10 mb-2"> <?=$site->newsletter_headline() ?> </h2>
      <p class="text-subheadline text-dead-pixel-10 mb-2"> <?=$site->newsletter_lead()?></p>
      <p class="text-body text-dead-pixel-10 mb-2"> <?=$site->newsletter_text()?></p>
    </div>  
    <div>
      <button class="primary" data-style="pill"><?=$site->newsletter_button_text()?></button>
      </div>
    </div>

    <div class="divider content_element_full"></div>

    <div class="content_element_full">
      <?php snippet("oveda", ["oveda_search" => $page->oveda(), "date_search" => false, "recurrent" => false]) ?>
    </div>

    <div class="divider content_element_full"></div>

    <!--- Projektupdates -->
    <?php if ($projectsPage = page('projects')): ?>
      <div class="projects content_element_full">
        <h2 class="text-title mb-3"> Projektupdates</h2>
      
        <ul class="content_grid">
          <?php foreach ($projectsPage->children()->children()->listed() as $project_update): ?>
            
            <?php snippet("components/project/projectUpdateCard", compact('project_update')) ?>

          <?php endforeach ?>
        </ul>
      </div>
    <?php endif ?>
  <?php endslot() ?> 

<?php endsnippet() ?>