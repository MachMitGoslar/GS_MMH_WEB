<?php

/**
 * Button: the one way to render a button or a button-styled link.
 * Link when href is set, otherwise a <button>. Look comes from buttons.css
 * (data-type, data-size, data-style, data-role); never write the markup of a
 * button by hand.
 *
 * @var string $label Text (HTML allowed)
 * @var string|null $href Renders a link
 * @var string|null $variant primary (default), secondary or tertiary
 * @var string|null $size small, regular (default) or large
 * @var string|null $shape pill (default), rounded-corners or square
 * @var string|null $role destructive or success (state colors from the tokens)
 * @var string|null $icon Icon key placed before the label, see utilities/icon.php
 * @var string|null $type Button type when there is no href (default: button)
 * @var string|null $class Extra classes
 * @var array $attrs Further attributes, e.g. ['target' => '_blank', 'rel' => 'noopener']; true = bare attribute, false/null = left out
 */

$label ??= '';
$href ??= null;
$variant ??= 'primary';
$size ??= 'regular';
$shape ??= 'pill';
$role ??= null;
$icon ??= null;
$type ??= 'button';
$class ??= null;
$attrs ??= [];

$attrHtml = '';
foreach ($attrs as $name => $value) {
    if ($value === false || $value === null) {
        continue;
    }
    $attrHtml .= $value === true
        ? ' ' . esc($name, 'attr')
        : ' ' . esc($name, 'attr') . '="' . esc($value) . '"';
}

$tag = $href !== null ? 'a' : 'button';
$common = ' class="' . esc(trim('gs-c-btn ' . ($class ?? ''))) . '" data-type="' . esc($variant) . '" data-size="' . esc($size) . '" data-style="' . esc($shape) . '"'
    . ($role ? ' data-role="' . esc($role) . '"' : '');
$target = $href !== null ? ' href="' . esc($href) . '"' : ' type="' . esc($type) . '"';
?>
<<?= $tag ?><?= $target ?><?= $common ?><?= $attrHtml ?>><?php if ($icon) :
    snippet('utilities/icon', ['name' => $icon, 'size' => 16]);
 endif ?><?= $label ?></<?= $tag ?>>
