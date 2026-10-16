<?php

/**
 * Card list block: pages as cards, each in the card that fits its type. The
 * cards come from the shared card layout (utilities/card); this block only
 * picks the pages and the card snippet.
 *
 * @var \Kirby\Cms\Block $block
 */

use Kirby\Cms\Pages;

$pages = $block->source()->value() === 'children'
    ? (($parent = $block->parentPage()->toPage()) ? $parent->children()->listed() : new Pages([]))
    : $block->pages()->toPages();

$pages = match ($block->sortBy()->value()) {
    'newest' => $pages->sortBy('modified', 'desc'),
    'title' => $pages->sortBy('title', 'asc'),
    default => $pages,
};

if ($block->limit()->isNotEmpty()) {
    $pages = $pages->limit($block->limit()->toInt());
}

if ($pages->isEmpty()) {
    return;
}
?>
<?php if ($block->title()->isNotEmpty()) : ?>
  <h2 class="font-title mb-3"><?= $block->title()->html() ?></h2>
<?php endif ?>
<?php snippet('utilities/card-list', ['pages' => $pages, 'columns' => (int) $block->columns()->or(3)->value(), 'featured' => $block->featured()->or('none')->value()]) ?>
