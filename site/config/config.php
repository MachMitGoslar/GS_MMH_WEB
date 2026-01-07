<?php

/**
 * Kirby 5 Configuration
 * https://getkirby.com/docs/reference/system/options
 */

// Load site helper functions
require_once __DIR__ . '/../helpers.php';

return [
    'debug' => true,
    'panel' => [
        'install' => true,
        'slug' => 'panel',
    ],

    // Load custom routes
    'routes' => require __DIR__ . '/routes.php',

    // Load custom hooks
    'hooks' => require __DIR__ . '/hooks.php',
];
