<?php
/*
  Snippets are a great way to store code snippets for reuse
  or to keep your templates clean.

  This layouts snippet renders the content of a layout
  field with our custom grid system.

  More about snippets:
  https://getkirby.com/docs/guide/templates/snippets
*/
?>
<?php foreach ($field->toLayouts() as $layout): ?>
<section class="grid gap-4 grid-cols-<?=count($layout->columns())?>" id="<?= esc($layout->id(), 'attr') ?>">
  <?php foreach ($layout->columns() as $column): ?>
  <div class="col-span-1" style="--columns:<?= esc($column->span(), 'css') ?>">
    <div class="text">
      <?= $column->blocks() ?>
    </div>
  </div>
  <?php endforeach ?>
</section>
<?php endforeach ?>
