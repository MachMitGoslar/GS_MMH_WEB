<?php

/**
 * Block catalog: every block type in every variant (Mmh\Site\Catalog).
 * For editors only, not linked.
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 * @var array $groups Mmh\Site\Catalog::entries()
 */

use Mmh\Site\Catalog;

?>
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main class="main">
  <section class="grid content mb-7">
    <div class="grid-item" data-span="1/1">
      <h1>Block-Katalog</h1>
      <p>Jeder Blocktyp in jeder Variante, mit Beispieldaten. Nur für Redakteurinnen und Redakteure.</p>
    </div>

    <?php foreach ($groups as $type => $entries) : ?>
      <div class="grid-item" data-span="1/1" id="<?= esc($type, 'attr') ?>">
        <h2><?= esc($type) ?></h2>
      </div>
      <?php foreach ($entries as $entry) : ?>
        <div class="grid-item" data-span="1/1">
          <h3><?= esc($entry['label']) ?></h3>
          <?php snippet('utilities/blocks', ['blocks' => Catalog::blocks($type, $entry['content'], $page)]) ?>
        </div>
      <?php endforeach ?>
    <?php endforeach ?>
  </section>
</main>
<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
