<?php

/**
 * Hero block: a wide image band (same look as the cover image under the
 * header). Pages show their cover automatically; this block is for an image
 * in the middle of the content.
 *
 * @var \Kirby\Cms\Block $block
 */

$image = $block->image()->toFile();

if ($image === null) {
    return;
}

snippet('sections/hero', [
    'image' => $image,
    'variant' => $block->variant()->or('wide')->value(),
]);
