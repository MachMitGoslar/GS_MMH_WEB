<?php

/** @var \Kirby\Cms\Block $block */
?>
<hr
    class="c-divider"
    data-line-style="<?= esc($block->lineStyle()->or('solid')) ?>"
    data-thickness="<?= esc($block->thickness()->or('thin')) ?>"
    data-color="<?= esc($block->color()->or('light')) ?>"
    data-spacing="<?= esc($block->spacing()->or('regular')) ?>"
>
