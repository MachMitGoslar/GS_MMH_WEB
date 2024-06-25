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
    <?php snippet('layouts', ['field' => $page->layout()])  ?>

    <aside class="contact mt-4 w-screen text-gray-700">
      <h2 class=" font-black text-2xl">Unsere Kontaktdaten</h2>
      <div class="grid grid-cols-3 gap-3">
        <section class="column text">
          <h3 class="font-thin" >Anschrift</h3>
          <?= $page->address() ?>
        </section>
        <section class="column text">
          <h3 class="font-thin" >Email</h3>
          <p><?= Html::email($page->email()) ?></p>
          <h3 class="font-thin" >Telefonisch</h3>
          <p><?= Html::tel($page->phone()) ?></p>
        </section>
        <section class="column text">
          <h3 class="font-thin" >online</h3>
          <ul>
            <?php foreach ($page->social()->toStructure() as $social): ?>
            <li><?= Html::a($social->url(), $social->platform()) ?></li>
            <?php endforeach ?>
          </ul>
        </section>
      </div>
    </aside>      
  <?php endslot() ?> 

<?php endsnippet() ?>