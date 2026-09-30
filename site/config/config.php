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
    // Muss mit den Optionen von `topic`
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

    // Mapbox Access Token (öffentlicher pk.-Token für mmh.goslar.de und machmit.goslar.de;
    // im Mapbox-Dashboard auf diese URLs beschränken). Pro Host überschreibbar.
    'mmh.mapbox.token' => 'pk.eyJ1IjoicmFuZ2FyaWFuIiwiYSI6ImNtdW56cGFreTAwOTQyd3IwNnd6YXBocHcifQ.lI4peTIwuo7Fak6Ng8r-Zw',

    // Bildoptimierung: Qualität + Srcset-Breiten je Verwendungszweck.
    // Driver/Binary stehen in den host-spezifischen Configs.
    // Jede Rolle gibt es als Original-Format und als WebP-Variante ("<rolle>-webp").
    'thumbs' => [
        'quality' => 78,
        'interlace' => true,
        'srcsets' => (function () {
            $roles = [
                'hero' => [640, 1024, 1600, 1920],
                'content' => [400, 800, 1200],
                'card' => [320, 480, 640],
                'thumb' => [200, 400],
            ];
            $srcsets = [];
            foreach ($roles as $role => $widths) {
                foreach ($widths as $width) {
                    $srcsets[$role][$width . 'w'] = ['width' => $width];
                    $srcsets[$role . '-webp'][$width . 'w'] = ['width' => $width, 'format' => 'webp'];
                }
            }
            return $srcsets;
        })(),
    ],

    // Load custom API routes (higher priority)
    'api' => require __DIR__ . '/api.php',

    // Load custom routes
    'routes' => require __DIR__ . '/routes.php',

    // Load custom hooks
    'hooks' => require __DIR__ . '/hooks.php',

];
