<?php

/**
 * Layouts: renders the visible layouts as grid rows with their columns and blocks.
 *
 * @var \Kirby\Cms\Layouts $layouts
 * @var string $wrapperClass Classes of each row (default: "grid content")
 * @var bool $highlight Adds data-highlight from the layout's highlight field
 */

$wrapperClass = $wrapperClass ?? 'grid content';
$highlight = $highlight ?? false;

foreach ($layouts as $layout) {
    if (!$layout->isVisible()) {
        continue;
    }
    ?>
<div class="<?= esc($wrapperClass) ?>"<?= $highlight ? ' data-highlight="' . esc($layout->highlight()->or('none')) . '"' : '' ?>>
    <?php foreach ($layout->columns() as $column) : ?>
    <div class="grid-item" data-span="<?= $column->width() ?>">
        <?php snippet('utilities/blocks', ['blocks' => $column->blocks()]) ?>
    </div>
    <?php endforeach ?>
</div>
    <?php
}
