<?php

/**
 * Focus point of an image for one usage.
 *
 * Sits next to an image field (`image: <field name>`) and stores "x% y%"
 * like Kirby's own focus. Empty = the focus of the file (set in the
 * Mediathek) applies. The Vue component lives in index.js.
 */

return [
    'props' => [
        // Name of the sibling files field that holds the image
        'image' => fn (?string $image = null) => $image,
        'value' => fn ($value = null) => is_string($value) ? trim($value) : null,
    ],
    'computed' => [
        // Image of the saved content, for the first render before the
        // form values are available
        'savedImage' => function () {
            $name = $this->image;
            if (!$name || !method_exists($this->model(), 'content')) {
                return null;
            }

            $file = $this->model()->content()->get($name)->toFile();

            return $file ? [
                'url' => $file->url(),
                'focus' => $file->focus()->value(),
            ] : null;
        },
    ],
    'validations' => [
        'focus' => function ($value) {
            if ($value === null || $value === '') {
                return true;
            }

            if (!preg_match('/^\d{1,3}(\.\d+)?% \d{1,3}(\.\d+)?%$/', (string) $value)) {
                throw new Kirby\Exception\InvalidArgumentException('Ungültiger Fokuspunkt');
            }

            return true;
        },
    ],
    'save' => fn ($value) => is_string($value) && $value !== '' ? $value : null,
];
