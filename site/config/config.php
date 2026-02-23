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

    // Google Calendar Integration (optional)
    // To enable: Create a Google Cloud Service Account, download the JSON key,
    // and set the path here. Share each room's calendar with the service account email.
    'google' => [
        'calendar' => [
            'credentials' => null, // e.g., __DIR__ . '/google-service-account.json'
        ],
    ],

    // Load custom API routes (higher priority)
    'api' => require __DIR__ . '/api.php',

    // Load custom routes
    'routes' => require __DIR__ . '/routes.php',

    // Load custom hooks
    'hooks' => require __DIR__ . '/hooks.php',

];
