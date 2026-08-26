<?php

use PHPMailer\PHPMailer\SMTP;

/**
 * This config file is for local ddev usage
 * usually you'd like to turn off debugging on the /config.php file and activate it for local or development sites
 * same foes for installing the panel (creating accounts)
 */

return [
    'ready' => function () {
        return [
            'debug' => false,
            'panel' => [
                'install' => true,
                'vue.compiler' => false,
            ],
            'db' => [
                'host' => 'db:3306',
                'database' => 'db',
                'user' => 'db',
                'password' => 'db',
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
                    'type' => 'sendmail', // Transporttyp aus .env.local
                    'host' => 'localhost',   // SMTP-Host aus .env.local
                    'port' => 1025, // SMTP-Port aus .env.local, Standard 1025 für MailHog
                    'security' => '', // Sicherheit aus .env.local
                    'auth' => '', // Authentifizierung aus .env.local
                    'username' => 'machmit', // Benutzername aus .env.local
                    'password' => 'test', // Passwort aus .env.local
                ],
                'from' => 'noreply@gs-mmh-web.ddev.site',
            ],
            'bnomei.dotenv.environment' => function () {
                return 'local';
            },
            // Settings for the DreamForm plugin
            'tobimori.dreamform' => [
                'storeSubmissions' => true,
                'log' => true,
                'email' => [
                    'from' => 'machmit@goslar.de',
                    'name' => 'MachMit!Website',
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
            //"Kirby\Http\Cookie::$key" => env('COOKIE_KEY'),
        ];
    },
];
