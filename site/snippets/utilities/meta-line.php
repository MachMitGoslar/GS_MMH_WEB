<?php

/**
 * Meta line: one row above a title, with something classifying on the left
 * (status badge, kind, date) and something secondary on the right (date, tags).
 * Slots: start, end (both optional). Look in meta-line.css.
 *
 * <?php snippet('utilities/meta-line', slots: true) ?>
 *   <?php slot('start') ?> ...badge... <?php endslot() ?>
 *   <?php slot('end') ?> ...date... <?php endslot() ?>
 * <?php endsnippet() ?>
 *
 * @var \Kirby\Template\Slots $slots
 */
?>
<div class="c-meta-line">
  <?php if ($start = $slots->start()) : ?>
    <div class="c-meta-line__start"><?= $start ?></div>
  <?php endif ?>
  <?php if ($end = $slots->end()) : ?>
    <div class="c-meta-line__end"><?= $end ?></div>
  <?php endif ?>
</div>
