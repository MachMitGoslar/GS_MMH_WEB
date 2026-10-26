<?php
/**
 * Divider snippet
 * @var string $lineStyle
 * @var string $thickness
 * @var enum-string $color
 * @var string $spacing
 */

$lineStyle = $lineStyle ?? 'solid';
$thickness = $thickness ?? 'thin';
$color = $color ?? 'light';
$spacing = $spacing ?? 'regular';

?>
<hr
    class="c-divider"
    data-line-style="<?= esc($lineStyle) ?>"
    data-thickness="<?= esc($thickness) ?>"
    data-color="<?= esc($color) ?>"
    data-spacing="<?= esc($spacing) ?>"
>