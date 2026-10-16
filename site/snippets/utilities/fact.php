<?php

/**
 * Fact: icon, label and value, the one block for facts and contact
 * details (event facts, member contact, contact page, editor block). Group in
 * utilities/facts.
 *
 * @var string $icon Icon key, see utilities/icon.php
 * @var string|null $label Small caption above the value (HTML)
 * @var string $value The value (HTML)
 * @var string|null $href Makes the value a link (mailto:, tel:, https:)
 * @var bool $external Opens the value link in a new tab
 * @var array|null $link A further link below the value: ['href' => ..., 'label' => ..., 'external' => true]
 */

$icon ??= 'info';
$label ??= null;
$value ??= '';
$href ??= null;
$external ??= false;
$link ??= null;

$newTab = static fn (bool $on): string => $on ? ' target="_blank" rel="noopener noreferrer"' : '';
?>
<div class="c-fact">
  <span class="c-fact__icon"><?php snippet('utilities/icon', ['name' => $icon, 'size' => 18]) ?></span>
  <span class="c-fact__body">
    <?php if ($label) : ?>
      <span class="c-fact__label"><?= $label ?></span>
    <?php endif ?>
    <?php if ($href) : ?>
      <a class="c-fact__value" href="<?= esc($href) ?>"<?= $newTab($external) ?>><?= $value ?></a>
    <?php else : ?>
      <span class="c-fact__value"><?= $value ?></span>
    <?php endif ?>
    <?php if ($link && !empty($link['href'])) : ?>
      <a class="c-fact__link" href="<?= esc($link['href']) ?>"<?= $newTab($link['external'] ?? false) ?>>
        <?= $link['label'] ?? 'Öffnen' ?>
        <?php snippet('utilities/icon', ['name' => 'external-link', 'size' => 14]) ?>
      </a>
    <?php endif ?>
  </span>
</div>
