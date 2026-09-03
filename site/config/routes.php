<?php

/**
 * Site Routes Configuration
 *
 * Define custom routes for the MachMit!Haus website
 */

use GsMmh\WebPlugin\NewsletterRecipients;
use Kirby\Cms\Response;
use Kirby\Database\Db;
use Kirby\Http\Exceptions\NextRouteException;

require_once __DIR__ . '/../controllers/events-api.php';
require_once __DIR__ . '/../controllers/oveda-event.php';

/**
 * Projekte, die als echte Dublette in einem anderen Projekt aufgegangen sind.
 * Alter Slug => neuer Pfad inklusive Kapitel-Anker.
 *
 * Thematisch verwandte, aber eigenstaendige Projekte werden hier bewusst NICHT
 * gefuehrt: sie haben eigene Seiten und werden ueber das Themenfeld gruppiert.
 */
const MMH_MERGED_PROJECTS = [
    'machmitwald-christmas-challenge' => 'projects/machmitwald#kapitel-machmitwald-christmas-challenge',
    'mein-buntes-goslar' => 'projects/burgerkunst-mein-buntes-goslar#kapitel-mein-buntes-goslar',
    'so-ist-tet' => 'projects/interkulturelle-begegnungen#kapitel-so-ist-tet',
];

return [

    /**
     * 301 für zusammengeführte Projekte. Greift unter beiden Wurzeln,
     * weil Projekte je nach Status unter projects/ oder project-archive/ lagen.
     */
    [
        'pattern' => ['projects/(:any)', 'project-archive/(:any)'],
        'action' => function (string $slug) {
            if (isset(MMH_MERGED_PROJECTS[$slug])) {
                go(MMH_MERGED_PROJECTS[$slug], 301);
            }

            throw new NextRouteException();
        },
    ],
    [
        // iCalendar download for a single Oveda event date
        'pattern' => 'events/(:num).ics',
        'method' => 'GET',
        'action' => function (string $eventDateId) {
            $detail = mmhOvedaEventDetail((int) $eventDateId);

            if ($detail === null) {
                throw new NextRouteException();
            }

            return new Response(mmhOvedaEventIcs($detail), 'text/calendar', 200, [
                'Content-Disposition' => 'attachment; filename="veranstaltung-' . $detail['id'] . '.ics"',
            ]);
        },
    ],
    [
        // Detail view for a single Oveda event date
        'pattern' => 'events/(:num)',
        'method' => 'GET',
        'action' => function (string $eventDateId) {
            $detail = mmhOvedaEventDetail((int) $eventDateId);

            if ($detail === null) {
                throw new NextRouteException();
            }

            $parent = site()->find('events');

            $page = Kirby\Cms\Page::factory([
                'slug' => (string) $detail['id'],
                'template' => 'event',
                'model' => 'event',
                'parent' => $parent,
                'num' => null,
                'content' => [
                    'title' => $detail['title'],
                    'headline' => $detail['title'],
                    'seo_description' => Kirby\Toolkit\Str::excerpt(
                        strip_tags($detail['description']),
                        160,
                        false,
                    ),
                    'social_image_url' => $detail['photo']['url'] ?? '',
                    'robots' => $detail['is_past'] ? 'noindex' : 'index',
                ],
            ]);

            return $page->render(['detail' => $detail]);
        },
    ],
    [
        'pattern' => 'events.json',
        'method' => 'GET',
        'action' => function () {
            $payload = json_encode(
                mmhEventsApiPayload(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            );

            return new Response(
                $payload,
                'application/json',
            );
        },
    ],
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
	<script
	    type="text/javascript"
	    src="https://freiwilligendatenbank.aktion-mensch.de/app/engagementplattform-loader-angebotswidget.js"
	></script>
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
     * Newsletter Subscription
     * Adds a new subscriber to the newsletter_recipients table
     */
    [
        'pattern' => 'newsletter-anmelden.json',
        'method' => 'POST',
        'action' => function () {
            try {
                NewsletterRecipients::create([
                    'first_name' => kirby()->request()->get('first_name'),
                    'last_name' => kirby()->request()->get('last_name'),
                    'email' => kirby()->request()->get('email'),
                ]);

                return new Response(
                    json_encode(['success' => true, 'message' => 'Danke! Du wirst ab sofort über unsere Neuigkeiten informiert.'], JSON_UNESCAPED_UNICODE),
                    'application/json',
                );
            } catch (\Throwable $e) {
                return new Response(
                    json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE),
                    'application/json',
                    400,
                );
            }
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
