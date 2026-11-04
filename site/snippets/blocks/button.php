<?php

/** @var \Kirby\Cms\Block $block */

// Felder aus button.yml
$link = $block->link()->toUrl() ?? '';
$text = $block->linktext()->or('Button');
$target = $block->target()->toBool();

// Strukturfeld "buttontype"
$type = $block->buttontype()->toObject();

$color = $type->color()->or('primary');
$size = $type->size()->or('regular');
$style = $type->style()->or('pill');

// Target behandeln
$targetAttr = $target ? ' target="_blank" rel="noopener"' : '';
?>

<a
        href="<?= esc($link) ?>"
        class="gs-c-btn"
        data-button="true"
        data-type="<?= esc($color) ?>"
        data-size="<?= esc($size) ?>"
        data-style="<?= esc($style) ?>"
    <?= $targetAttr ?>
>
    <?= esc($text) ?>
</a>
