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
            'debug' => true,
            'panel' => [
                'install' => true,
            ],
            'db' => [
                'host' => "db",
                'database' => "mmh_dev_db",
                'user' => "mmh_dev_user",
                'password' => "mmh_dev_user",
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
                    'type' => 'smtp', // Transporttyp aus .env.local
                    'host' => 'localhost',   // SMTP-Host aus .env.local
                    'port' => 1025, // SMTP-Port aus .env.local, Standard 1025 für MailHog
                    'security' => null, // Sicherheit aus .env.local
                    'auth' => null, // Authentifizierung aus .env.local
                    'username' => null, // Benutzername aus .env.local
                    'password' => null // Passwort aus .env.local
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
                    'from' => 'no-reply@gs-mmh-web.ddev.site',
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
    }
];