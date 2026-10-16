<?php

/**
 * Chip: a light pill for tags and short facts next to a title. A link when it
 * has an href. Not a status (that is utilities/badge) and not an action (that
 * is a button).
 *
 * Themes (see chip.css): default, accent, free, tag (outlined, "#" in front).
 *
 * @var string $label Text (HTML allowed)
 * @var string|null $href Makes the chip a link
 * @var string|null $theme Color theme (default: default)
 * @var string|null $size regular (default) or small
 */

$label ??= '';
$href ??= null;
$theme ??= 'default';
$size ??= 'regular';
$tag = $href ? 'a' : 'span';
?>
<<?= $tag ?> class="c-chip" data-theme="<?= esc($theme) ?>" data-size="<?= esc($size) ?>"<?= $href ? ' href="' . esc($href) . '"' : '' ?>><?= $label ?></<?= $tag ?>>
