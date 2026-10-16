<?php

/**
 * Badge: a small label, optionally with an icon (and a second label for a
 * change "A → B"). The one atom for status, kind and team labels; colors come
 * from the theme, never from the caller.
 *
 * Themes (see badge.css): default, primary, secondary, success, warning, error,
 * highlight, project-planning|preparing|active|review|done, recap|current|
 * upcoming|news (newsletter), team-staff|volunteer|partner|issuer.
 *
 * @var string $label Text (HTML allowed)
 * @var string|null $theme Color theme (default: default)
 * @var string|null $icon Icon key, see utilities/icon.php
 * @var string|null $to Second label: renders "label → to" with a gradient to $themeTo
 * @var string|null $themeTo Theme of the second label
 * @var string|null $size regular (default) or large
 * @var string|null $class Extra classes
 * @var string|null $tag Element (default: span)
 */

$label ??= '';
$theme ??= 'default';
$icon ??= null;
$to ??= null;
$themeTo ??= $theme;
$size ??= 'regular';
$class ??= null;
$tag ??= 'span';

$isChange = $to !== null && $to !== '';
$style = $isChange
    ? ' style="--badge-bg: linear-gradient(90deg, var(--badge-bg-' . esc($theme, 'attr') . '), var(--badge-bg-' . esc($themeTo, 'attr') . '));"'
    : '';
?>
<<?= $tag ?> class="<?= esc(trim('c-badge ' . ($class ?? ''))) ?>" data-theme="<?= esc($theme) ?>" data-size="<?= esc($size) ?>"<?= $style ?>>
  <?php if ($icon) :
        snippet('utilities/icon', ['name' => $icon, 'size' => 14]);
  endif ?>
  <span><?= $label ?></span>
  <?php if ($isChange) : ?>
        <?php snippet('utilities/icon', ['name' => 'arrow-right', 'size' => 14]) ?>
    <span><?= $to ?></span>
  <?php endif ?>
</<?= $tag ?>>
