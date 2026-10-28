<?php

use Kirby\Cms\Page;
/**
 * Site Routes Configuration
 *
 * Define custom routes for the MachMit!Haus website
 */

use Kirby\Cms\Response;
use Kirby\Database\Db;
use Kirby\Http\Exceptions\NextRouteException;

return [
    /**
     * Horoscope Card API
     * Returns the daily Goslarer Horoskope as a JSON app-card payload.
     * The `/app/(:any)` tracker below hands off via NextRouteException,
     * so the order of these routes does not matter.
     */
    [
        'pattern' => '/app/horoskop_card',
        'action' => function () {
            $content = snippet('content-types/horoskope/card', [], true);

            return new Response($content, 'application/json');
        },
    ],

    /**
     * Horoskope List Page
     * Renders the daily Goslarer Horoskope as an HTML list with
     * collapsible texts per zodiac sign.
     * The `/app/(:any)` tracker below hands off via NextRouteException,
     * so the order of these routes does not matter.
     */
    [
        'pattern' => '/app/horoskope',
        'action' => function () {
            $content = snippet('content-types/horoskope/list', [], true);

            return new Response($content, 'text/html');
        },
    ],

    /**
     * App Request Tracking
     * Increments a per-URL/per-day counter for any /app/* request, then
     * hands the request off to the next matching route so the specific
     * /app/<endpoint> routes below can produce the actual response.
     *
     * Any thrown DB error is swallowed — tracking must never break an
     * endpoint the mobile app depends on.
     */
    [
        'pattern' => '/app/(:any)',
        'action' => function ($any) {
            try {
                Db::execute('CREATE TABLE IF NOT EXISTS `app_requests` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `url` varchar(255) NOT NULL,
                    `day` date NOT NULL,
                    `requests` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

                $data = [
                    'url' => $any,
                    'day' => date('Y-m-d'),
                ];

                if ($app_request = Db::first('app_requests', '*', $data)) {
                    $data['requests'] = $app_request->requests() + 1;
                    Db::update('app_requests', $data, [
                        'url' => $data['url'],
                        'day' => $data['day'],
                    ]);
                } else {
                    $data['requests'] = 1;
                    Db::insert('app_requests', $data);
                }
            } catch (\Throwable) {
                // tracking is best-effort; never break the actual endpoint
            }

            throw new NextRouteException();
        },
    ],

    /**
     * Ferienpass Events API - Random Event
     * Returns a random ferienpass event in JSON format
     */
    [
        'pattern' => '/app/ferienpass.json',
        'action' => function () {
            $query = get('data') ?: 74; // default to program 74 if no query provided
            $content = snippet('content-types/ferienpass/event_random', ['query' => $query], true);

            return new Response($content, 'application/json');
        },
    ],

    /**
     * Ferienpass Events API - All Events
     * Returns all ferienpass events in JSON format
     */
    [
        'pattern' => '/app/ferienpass_index.json',
        'action' => function () {
            $query = get('data') ?: 74; // default to program 74 if no query provided
            $content = snippet('content-types/ferienpass/events', ['query' => $query], true);

            return new Response($content, 'application/json');
        },
    ],
];
