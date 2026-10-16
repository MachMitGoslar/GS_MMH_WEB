<?php

/**
 * Facts: a group of utilities/fact items (icon, label, value). The items are the slot.
 *
 * <?php snippet('utilities/facts', ['columns' => 2, 'surface' => 'card'], slots: true) ?>
 *   <?php snippet('utilities/fact', [...]) ?>
 * <?php endsnippet() ?>
 *
 * @var int|null $columns 1 or 2 (default 2; always 1 on small screens)
 * @var string|null $surface card (white box with border, default) or plain
 * @var string|null $class Extra classes (e.g. to place it in a grid)
 * @var \Kirby\Template\Slots $slots
 */

$columns ??= 2;
$surface ??= 'card';
$class ??= null;
?>
<div class="<?= esc(trim('c-facts ' . ($class ?? ''))) ?>" data-columns="<?= (int) $columns ?>" data-surface="<?= esc($surface) ?>">
  <?= $slot ?>
</div>
