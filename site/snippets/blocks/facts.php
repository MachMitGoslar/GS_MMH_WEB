<?php

/**
 * Facts block ("Auf einen Blick"): icon, label and value rows (utilities/fact),
 * optionally with a map beside them (sections/glance). Same look as event
 * facts and the contact page. Without a map: the plain fact group.
 *
 * @var \Kirby\Cms\Block $block
 */

$items = $block->items()->toStructure();

if ($items->isEmpty()) {
    return;
}

$token = mmhMapboxToken();
$hasMap = $block->map()->toBool()
    && $token !== ''
    && $block->lat()->isNotEmpty()
    && $block->lng()->isNotEmpty();

$facts = function () use ($items) {
    foreach ($items as $item) {
        $href = $item->link()->isNotEmpty() ? $item->link()->toUrl() : null;
        snippet('utilities/fact', [
            'icon' => $item->icon()->or('info')->value(),
            'label' => $item->label()->isNotEmpty() ? $item->label()->html() : null,
            'value' => $item->value()->html(),
            'href' => $href,
            'external' => $href !== null && str_starts_with($href, 'http') && !str_starts_with($href, kirby()->url()),
        ]);
    }
};

if ($hasMap) {
    // The block brings its own map assets (mapbox once per page, the script after the markup)
    $firstMap = mmhOnce('map-assets');
    if ($firstMap) {
        snippet('layout/mapbox');
    }

    snippet('sections/glance', [
        'map' => [
            'lat' => $block->lat()->value(),
            'lng' => $block->lng()->value(),
            'token' => $token,
            'label' => $block->mapLabel()->value(),
            'aria' => 'Karte' . ($block->mapLabel()->isNotEmpty() ? ': ' . $block->mapLabel()->value() : ''),
            'href' => $block->mapHref()->isNotEmpty() ? $block->mapHref()->value() : null,
        ],
    ], slots: true);
    $facts();
    endsnippet();

    if ($firstMap) {
        echo js('assets/js/event-map.js?version=' . filemtime(kirby()->root('index') . '/assets/js/event-map.js'));
    }

    return;
}

snippet('utilities/facts', [
    'columns' => (int) $block->columns()->or(2)->value(),
    'surface' => $block->surface()->or('card')->value(),
], slots: true);
$facts();
endsnippet();
