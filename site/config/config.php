<?php

/**
 * Kirby 5 Configuration
 * https://getkirby.com/docs/reference/system/options
 */

// Load site helper functions
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/newsletter-email.php';

return [
    'debug' => true,

    'panel' => [
        'install' => true,
        'slug' => 'panel',
    ],

    'date.timezone' => 'Europe/Berlin',

    // Themenfelder der Projekte. Slug => Label.
    // Muss mit den Optionen von `topic` / `topics_secondary`
    // in site/blueprints/pages/project.yml übereinstimmen.
    'mmh.topics' => [
        'digitale-stadt' => 'Digitale Stadt',
        'teilhabe' => 'Teilhabe & Barrierefreiheit',
        'klima' => 'Klima & Nachhaltigkeit',
        'ehrenamt' => 'Ehrenamt & Engagement',
        'demokratie' => 'Demokratie & Beteiligung',
        'kultur' => 'Kunst, Kultur & Begegnung',
        'haus' => 'Das MachMit!Haus',
    ],

    'cache.oveda' => true,

    // Load custom API routes (higher priority)
    'api' => require __DIR__ . '/api.php',

    // Load custom routes
    'routes' => require __DIR__ . '/routes.php',

    // Load custom hooks
    'hooks' => require __DIR__ . '/hooks.php',

];
