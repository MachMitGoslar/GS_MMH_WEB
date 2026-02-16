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

    // Mail settings (DDEV Mailpit)
    'email' => [
        'transport' => [
            'type' => 'smtp',
            'host' => 'localhost',
            'port' => 1025,
            'security' => false,
            'auth' => false,
            'username' => 'noreply@gs-mmh-web.ddev.site',
            'password' => 'Passwort123!',
        ],
        'from' => 'noreply@gs-mmh-web.ddev.site',
    ],


    // Settings for the DreamForm plugin
    'tobimori.dreamform' => [
        'storeSubmissions' => true,
        'log' => true,
        'email' => [
            'from' => 'noreply@gs-mmh-web.ddev.site',
            'name' => "MachMit!Haus",
        ],
        'guards' => [
            // activated guards
            'available' => [
                'honeypot',
                'ratelimit',
            ],

            // Honeypot settings
            'honeypot.availableFields' => [
                'website',
                'email',
                'name',
                'url',
                'birthdate',
            ],

            // RateLimit settings
            'ratelimit' => [
                'limit' => 10,   // maximum of 10 requests
                'interval' => 3,  // in 3 minutes
            ],
        ],
    ],
];
