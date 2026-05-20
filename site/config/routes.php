<?php

/**
 * Site Routes Configuration
 *
 * Define custom routes for the MachMit!Haus website
 */

use Kirby\Cms\Response;
use Kirby\Database\Db;
use Kirby\Http\Exceptions\NextRouteException;

return [
    [
        'pattern' => 'ehrentag-goslar',
        'action' => function () {
            return new Response(<<<HTML
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ehrenamt Goslar</title>
</head>
<body>
    <div data-engagement-plattform data-engagement-plattform-integration-key="vzPpwKUyog"></div>
<script type="text/javascript" src="https://freiwilligendatenbank.aktion-mensch.de/app/engagementplattform-loader-angebotswidget.js"></script>
</body>
</html>
HTML, 'text/html');
        },
    ],

    /**
     * Newsletter RSS Feed
     * Provides an RSS feed of published newsletters
     */
    [
        'pattern' => 'newsletter.xml',
        'action' => function () {
            $pages = site()->page('newsletter')->children()->listed();
            $parent = site()->page(path: 'newsletter');

            $content = snippet('content-types/newsletter/rss_feed', compact('pages', 'parent'), true);

            // Return response with correct header type
            return new Response($content, 'application/xml');
        },
    ],

    /**
     * Horoscope Card API
     * Returns the daily Goslarer Horoskope as a JSON app-card payload.
     * Defined before the `/app/(:any)` tracker so it wins route matching.
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
     * Defined before the `/app/(:any)` tracker so it wins route matching.
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

    /**
     * Room Booking Request API
     * Handles room booking form submissions
     */
    [
        'pattern' => 'booking-request.json',
        'method' => 'POST',
        'action' => function () {
            require_once kirby()->root('snippets') . '/content-types/rooms/bookingRequestHandler.php';

            $result = handleBookingRequest();

            return new Response(
                json_encode($result, JSON_UNESCAPED_UNICODE),
                'application/json',
                $result['success'] ? 200 : 400,
            );
        },
    ],
    /**
     * Room Booking Request API (GET fallback for debugging)
     */
    [
        'pattern' => 'booking-request.json',
        'method' => 'GET',
        'action' => function () {
            return new Response(
                json_encode(['status' => 'ok', 'message' => 'Booking API endpoint. Use POST to submit.']),
                'application/json',
            );
        },
    ],
];
