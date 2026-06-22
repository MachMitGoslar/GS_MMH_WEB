<?php

/**
 * The config file is optional. It accepts a return array with config options
 * Note: Never include more than one return statement, all options go within this single return array
 * In this example, we set debugging to true, so that errors are displayed onscreen.
 * This setting must be set to false in production.
 * All config options: https://getkirby.com/docs/reference/system/options
 */

require_once __DIR__ . '/../plugins/kirby3-dotenv/global.php';
loadenv();

return [
    'debug' => false,
    'panel' => [
        'install' => false,
        'vue' => [
            'compiler' => false,
        ],
    ],
    'cache.oveda' => true,
    'db' => [
        'host' => env('MMH_DB_Host'),
        'database' => env('MMH_DB_Database'),
        'user' => env('MMH_DB_User'),
        'password' => env('MMH_DB_Password'),
    ],
    'thumbs' => [
        'driver' => 'im',
        'bin' => '/usr/bin/convert',
    ],
    'content' => [
        'salt' => env('CONTENT_SALT'),
    ],
    'tobimori.dreamform' => [
        'storeSubmissions' => true,
        'log' => true,
        'email' => [
            'from' => env('EMAIL_FROM'),
            'name' => env('EMAIL_NAME'),
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
    ]

];
