<?php

/**
 * The config file is optional. It accepts a return array with config options
 * Note: Never include more than one return statement, all options go within this single return array
 * In this example, we set debugging to true, so that errors are displayed onscreen.
 * This setting must be set to false in production.
 * All config options: https://getkirby.com/docs/reference/system/options
 */


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
        'host' => getenv('MMH_DB_Host') ?: null,
        'database' => getenv('MMH_DB_Database') ?: null,
        'user' => getenv('MMH_DB_User') ?: null,
        'password' => getenv('MMH_DB_Password') ?: null,
    ],
    'thumbs' => [
        'driver' => 'im',
        'bin' => '/usr/bin/convert',
    ],
    'content' => [
        'salt' => getenv('CONTENT_SALT') ?: null,
    ],
    'mmh.mapbox.token' => '',
    'tobimori.dreamform' => [
        'storeSubmissions' => true,
        'log' => true,
        'email' => [
            'from' => getenv('EMAIL_FROM') ?: null,
            'name' => getenv('EMAIL_NAME') ?: null,
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
