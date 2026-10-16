<?php

/**
 * Page title: the one heading block of a page. Built from the type system
 * (fonts.css): title font-titleXXL, subtitle font-subheadline, eyebrow
 * font-subheadline. Spacing and tone live in page-title.css; callers only fill text.
 * The block carries no grid or margin classes: the surrounding layout places it
 * (e.g. <div class="grid-item" data-span="1/1">), the same way on every page.
 *
 * @var string $title Title (HTML), the page's h1
 * @var string|null $subtitle Subtitle (HTML)
 * @var string|null $eyebrow Small line above the title (HTML)
 * @var string|null $tone default, or inverse on a dark image
 * @var string|null $align start (default) or center
 * @var string|null $subtitleTag Element of the subtitle (default: p)
 * @var callable|null $before Metas above the title: date, tags, facts (utilities/chip, badge)
 * @var callable|null $after Metas below the subtitle: topics, team (utilities/chip, badge, avatars)
 */

$title ??= '';
$subtitle ??= null;
$eyebrow ??= null;
$tone ??= 'default';
$align ??= 'start';
$subtitleTag ??= 'p';
$before ??= null;
$after ??= null;

?>
<div class="c-page-title" data-tone="<?= esc($tone) ?>" data-align="<?= esc($align) ?>">
  <?php if ($before) : ?>
    <div class="c-page-title__meta c-page-title__meta--before"><?php $before() ?></div>
  <?php endif ?>
  <?php if ($eyebrow) : ?>
    <p class="c-page-title__eyebrow font-subheadline"><?= $eyebrow ?></p>
  <?php endif ?>
  <h1 class="c-page-title__title font-titleXXL"><?= $title ?></h1>
  <?php if ($subtitle) : ?>
    <<?= $subtitleTag ?> class="c-page-title__subtitle font-subheadline"><?= $subtitle ?></<?= $subtitleTag ?>>
  <?php endif ?>
  <?php if ($after) : ?>
    <div class="c-page-title__meta c-page-title__meta--after"><?php $after() ?></div>
  <?php endif ?>
</div>
