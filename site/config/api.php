<?php

use Mmh\Site\Newsletter\EmailHtml;

/**
 * Custom API Routes
 * These routes have higher priority and won't be intercepted by plugins
 */

/**
 * Mobile newsletter HTML as a download. Shared by both newsletter URLs.
 */
$newsletterHtmlResponse = function ($page) {
    if (!$page || $page->intendedTemplate()->name() !== 'newsletter') {
        return new Kirby\Cms\Response('Not found', 'text/plain', 404);
    }

    $content = EmailHtml::mobileHtml($page);
    $filename = preg_replace('/[^a-z0-9-]+/i', '-', $page->slug()) ?: 'newsletter';

    return new Kirby\Cms\Response($content, 'text/html', 200, [
        'Content-Disposition' => 'attachment; filename="' . strtolower($filename) . '.html"',
        'X-Content-Type-Options' => 'nosniff',
    ]);
};

return [
    'routes' => [
        [
            'pattern' => 'booking/submit',
            'method' => 'POST',
            'auth' => false,  // Allow unauthenticated access
            'action' => function () {
                require_once kirby()->root('snippets') . '/content-types/rooms/bookingRequestHandler.php';

                return handleBookingRequest();
            },
        ],
        [
            'pattern' => 'booking/submit',
            'method' => 'GET',
            'auth' => false,  // Allow unauthenticated access
            'action' => function () {
                return [
                    'status' => 'ok',
                    'message' => 'Booking API endpoint. Use POST to submit.',
                    'endpoint' => '/api/booking/submit',
                ];
            },
        ],
        /**
         * All-Rooms Availability API
         */
        [
            'pattern' => 'rooms/availability.json',
            'method' => 'GET',
            'auth' => false,
            'action' => function () {
                $kirby = kirby();
                $roomsPage = $kirby->site()->find('rooms');

                if (!$roomsPage || !$kirby->option('nextcloud.calendar_url')) {
                    return ['rooms' => []];
                }

                $weeks = max(1, min(16, (int) get('weeks', 12)));
                $includeTentative = get('tentative', '1') !== '0';
                $berlin = new \DateTimeZone('Europe/Berlin');
                $rangeStart = new \DateTime('today', $berlin);
                $rangeEnd = new \DateTime("+{$weeks} weeks", $berlin);
                $rangeEnd->setTime(23, 59, 59);

                require_once $kirby->root('snippets') . '/content-types/rooms/nextcloudCalendarIntegration.php';

                $palette = ['#e84237', '#5c7adb', '#5da05d', '#af63b1', '#fbc62e'];
                $rooms = [];
                $i = 0;

                foreach ($roomsPage->children()->listed() as $room) {
                    $resourceEmail = $room->nextcloud_resource_email()->value();
                    if (!$resourceEmail) {
                        continue;
                    }
                    $rooms[] = [
                        'slug' => $room->slug(),
                        'title' => $room->title()->value(),
                        'color' => $palette[$i % count($palette)],
                        'slots' => ncRoomBusySlots($resourceEmail, $rangeStart, $rangeEnd, $includeTentative),
                    ];
                    $i++;
                }

                return ['rooms' => $rooms];
            },
        ],
        /**
         * Per-Room Availability API
         */
        [
            'pattern' => 'rooms/(:any)/availability.json',
            'method' => 'GET',
            'auth' => false,
            'action' => function (string $roomSlug) {
                $kirby = kirby();
                $room = $kirby->site()->find('rooms/' . $roomSlug)
                      ?? $kirby->site()->find('raeume/' . $roomSlug);

                if (!$room || $room->intendedTemplate()->name() !== 'room') {
                    return new Kirby\Cms\Response(
                        json_encode(['error' => 'Room not found']),
                        'application/json',
                        404,
                    );
                }

                $resourceEmail = $room->nextcloud_resource_email()->value();
                if (!$resourceEmail || !$kirby->option('nextcloud.calendar_url')) {
                    return ['slots' => [], 'room' => $roomSlug];
                }

                $weeks = max(1, min(16, (int) get('weeks', 6)));
                $includeTentative = get('tentative', '1') !== '0';
                $berlin = new \DateTimeZone('Europe/Berlin');
                $rangeStart = new \DateTime('today', $berlin);
                $rangeEnd = new \DateTime("+{$weeks} weeks", $berlin);
                $rangeEnd->setTime(23, 59, 59);

                require_once $kirby->root('snippets') . '/content-types/rooms/nextcloudCalendarIntegration.php';
                $slots = ncRoomBusySlots($resourceEmail, $rangeStart, $rangeEnd, $includeTentative);

                return ['slots' => $slots, 'room' => $roomSlug];
            },
        ],
        /**
         * Download rendered mobile Newsletter HTML from the Panel
         */
        [
            'pattern' => 'newsletter-html/(:all).html',
            'method' => 'GET',
            'auth' => false,
            'action' => function ($panelId) use ($newsletterHtmlResponse) {
                return $newsletterHtmlResponse(page(str_replace('+', '/', $panelId)));
            },
        ],
        /**
         * Download rendered mobile Newsletter HTML by slug
         */
        [
            'pattern' => 'newsletter/(:any).html',
            'method' => 'GET',
            'auth' => false,
            'action' => function ($slug) use ($newsletterHtmlResponse) {
                return $newsletterHtmlResponse(page('newsletter/' . $slug));
            },
        ],
    ],
];
