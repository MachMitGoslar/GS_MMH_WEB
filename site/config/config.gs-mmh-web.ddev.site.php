<?php

/**
 * This config file is for local ddev usage
 * usually you'd like to turn off debugging on the /config.php file and activate it for local or development sites
 * same foes for installing the panel (creating accounts)
 */
return [
    'debug' => true,
    'panel' => [
        'install' => true,
    ],
    'cache' => [
        'pages' => [
            'active' => false,
        ],
        'assets' => [
            'active' => true,
        ],
    ],
    'thumbs' => [
        'driver' => 'im',
        'bin' => '/usr/bin/convert',
    ],
    'google' => [
        'calendar' => [
            'credentials' => __DIR__ . '/../../storage/calendar_key.json',
        ],
    ],
    'email' => [
        'transport' => [
            'type' => 'smtp',
            'host' => 'localhost',   // SMTP-Host im Container
            'port' => 1025,           // SMTP-Port
            'security' => false,       // TLS nicht nötig bei Mailpit
            'auth' => false,            // Auth aktivieren
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
