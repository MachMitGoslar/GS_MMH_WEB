<?php
/**
 * This config file is for local ddev usage
 * usually you'd like to turn off debugging on the /config.php file and activate it for local or development sites
 * same foes for installing the panel (creating accounts)
 */
require_once __DIR__ . '/../plugins/kirby3-dotenv/global.php';
loadenv();

return [
    'ready' => function () {
        return [
            'debug' => true,
            'panel' => [
                'install' => true,
            ],
            'db' => [
                'host' => getenv('MMH_DB_HOST'),
                'database' => getenv('MMH_DB_DATABASE'),
                'user' => getenv('MMH_DB_USER'),
                'password' => getenv('MMH_DB_PASSWORD'),
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
                    'type' => env('EMAIL_TYPE'), // Transporttyp aus .env.local
                    'host' => env('EMAIL_HOST'),   // SMTP-Host aus .env.local
                    'port' => env('EMAIL_PORT'), // SMTP-Port aus .env.local, Standard 1025 für MailHog
                    'security' => env('EMAIL_SECURITY'), // Sicherheit aus .env.local
                    'auth' => env('EMAIL_AUTH'), // Authentifizierung aus .env.local
                    'username' => env('EMAIL_USERNAME'), // Benutzername aus .env.local
                    'password' => env('EMAIL_PASSWORD') // Passwort aus .env.local
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
            ],
            //"Kirby\Http\Cookie::$key" => env('COOKIE_KEY'),
        ];
    }
];