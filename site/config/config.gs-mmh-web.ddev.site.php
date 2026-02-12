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
];
