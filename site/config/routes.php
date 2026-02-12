<?php

/**
 * Site Routes Configuration
 *
 * Define custom routes for the MachMit!Haus website
 */

use Kirby\Cms\Response;
use Kirby\Database\Db;

return [
    /**
     * Newsletter RSS Feed
     * Provides an RSS feed of published newsletters
     */
    [
        'pattern' => 'newsletter.xml',
        'action' => function () {
            $pages = site()->page("newsletter")->children()->listed();
            $parent = site()->page(path: "newsletter");

            $content = snippet('content-types/newsletter/rss_feed', compact('pages', 'parent'), true);

            // Return response with correct header type
            return new Response($content, 'application/xml');
        },
    ],

    /**
     * App Request Tracking
     * Tracks requests from the mobile app
     */
    [
        'pattern' => '/app/(:any)',
        'action' => function ($any) {
            $data['url'] = $any;
            $data['day'] = date("Y-m-d");

            if ($app_request = Db::first('app_requests', '*', ['url' => $data['url'], 'day' => $data['day']])) {
                $data['requests'] = $app_request->requests();

                return Db::update('app_requests', $data, ['url' => $data['url'], 'day' => $data['day']]);
            } else {
                $data['requests'] = 1;

                return Db::insert('app_requests', $data);
            }
        },
    ],

    /**
     * Ferienpass Events API - Random Event
     * Returns a random ferienpass event in JSON format
     */
    [
        'pattern' => '/app/ferienpass.json',
        'action' => function () {
            $content = snippet('content-types/ferienpass/event_random', [], true);

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
            $content = snippet('content-types/ferienpass/events', [], true);

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
                $result['success'] ? 200 : 400
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
                'application/json'
            );
        },
    ],
];
