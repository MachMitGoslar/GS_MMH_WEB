<?php

/**
 * Event Icon Snippet
 *
 * Thin wrapper around `utilities/icon` so the event views keep their existing
 * call signature while the actual icon set lives in one place.
 *
 * @var string $icon The icon key
 * @var int|null $size Edge length in pixels
 */

snippet('utilities/icon', [
    'name' => $icon,
    'size' => $size ?? 20,
]);
