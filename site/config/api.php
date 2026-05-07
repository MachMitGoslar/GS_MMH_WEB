<?php

/**
 * Custom API Routes
 * These routes have higher priority and won't be intercepted by plugins
 */
require_once __DIR__ . '/../controllers/latest-update.php';

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
                    $calendarUri = $room->nextcloud_calendar_uri()->or($room->nextcloud_resource_email())->value();
                    if (!$calendarUri) {
                        continue;
                    }
                    $rooms[] = [
                        'slug' => $room->slug(),
                        'title' => $room->title()->value(),
                        'color' => $palette[$i % count($palette)],
                        'slots' => ncRoomBusySlots($calendarUri, $rangeStart, $rangeEnd, $includeTentative),
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

                $calendarUri = $room->nextcloud_calendar_uri()->or($room->nextcloud_resource_email())->value();
                if (!$calendarUri || !$kirby->option('nextcloud.calendar_url')) {
                    return ['slots' => [], 'room' => $roomSlug];
                }

                $weeks = max(1, min(16, (int) get('weeks', 6)));
                $includeTentative = get('tentative', '1') !== '0';
                $berlin = new \DateTimeZone('Europe/Berlin');
                $rangeStart = new \DateTime('today', $berlin);
                $rangeEnd = new \DateTime("+{$weeks} weeks", $berlin);
                $rangeEnd->setTime(23, 59, 59);

                require_once $kirby->root('snippets') . '/content-types/rooms/nextcloudCalendarIntegration.php';
                $slots = ncRoomBusySlots($calendarUri, $rangeStart, $rangeEnd, $includeTentative);

                return ['slots' => $slots, 'room' => $roomSlug];
            },
        ],
        /**
         * Create Newsletter Image
         */
        [
        'pattern' => 'newsletter-cover/(:any).svg',
        'method' => 'GET',
        'auth' => false,
        'action' => function ($slug) {

            if (!$page = page('newsletter/' . $slug)) {
                return new Kirby\Cms\Response('Not found', 'text/plain', 404);
            }

            $title = $page->title()->value();
            $logo = url('assets/svg/RZ-RGB_MM!2_iv.svg');

            $svg = <<<SVG
    <svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630">
      <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:#5d4e37;stop-opacity:1" />
          <stop offset="50%" style="stop-color:#6b5b47;stop-opacity:1" />
          <stop offset="100%" style="stop-color:#4a3c28;stop-opacity:1" />
        </linearGradient>
      </defs>
    
      <!-- Background -->
      <rect width="100%" height="100%" fill="url(#grad)" />
    
      <!-- Logo  -->
      <image href="{$logo}" x="38.4%" y="20%" width="280" />
    
      <!-- Text -->
      <text x="50%" y="70%" text-anchor="middle" dominant-baseline="middle"
            font-family="Arial, sans-serif"
            font-size="60"
            font-weight="700"
            fill="#ffffff">
            Newsletter
      </text>
    
      <text x="50%" y="80%" text-anchor="middle"
            font-family="Arial, sans-serif"
            font-size="28"
            fill="#ffffff">
            {$title}
      </text>
    </svg>
    SVG;

            return new Kirby\Cms\Response($svg, 'image/svg+xml');
        },
        ],
        /**
             * Latest Update for Goslar App Kachel
             */
        [
            'pattern' => 'latest-update',
            'method' => 'GET',
            'auth' => false,
            'action' => function () {
                $data = latestUpdateData(true);

                if (!$data) {
                    return [
                        'status' => 'error',
                        'message' => 'Keine Updates gefunden',
                    ];
                }

                return $data;
            },
        ],
        /**
         * Endpoint 2nd view in app
         */
        [
            'pattern' => 'highlights',
            'method' => 'GET',
            'auth' => false,
            'action' => function () {

                // get updates dynamically
                $dynamic = latestUpdateData();

                if ($dynamic) {
                    $dynamic['id'] = 2;
                    unset($dynamic['widget_type']);
                }

                $homePage = page('home');
                $projectsPage = page('projects');
                $newsletterPage = page('newsletter');
                $diaryPage = page('notes');
                $aboutPage = page('uber-uns');

                $now = date('Y-m-d\TH:i');

                $coverUrl = function ($p) {
                    if (!$p) {
                        return null;
                    }
                    $coverFile = $p->content()->get('cover')?->toFile();

                    return $coverFile ? $coverFile->url() : null;
                };

                return [
                    [
                        'id' => 1,
                        'title' => 'Heute im MM!H',
                        'description' => 'Entdecke die heutigen Events im MachMit!Haus oder gelange zur Raumbuchung.',
                        'image_url' => $coverUrl($homePage),
                        'call_to_action_url' => $homePage?->url(),
                        'published_at' => $now,
                    ],

                    $dynamic, // Slot 2 = Latest Update

                    [
                        'id' => 3,
                        'title' => 'Projekte',
                        'description' => 'Alle MachMit!Projekte auf einem Blick',
                        'image_url' => $coverUrl($projectsPage),
                        'call_to_action_url' => $projectsPage?->url(),
                        'published_at' => $now,
                    ],
                    [
                        'id' => 4,
                        'title' => 'Newsletter',
                        'description' => 'Entdecke unseren Newsletter.',
                        'image_url' => $coverUrl($newsletterPage),
                        'call_to_action_url' => $newsletterPage?->url(),
                        'published_at' => $now,
                    ],
                    [
                        'id' => 5,
                        'title' => 'Tagebuch',
                        'description' => 'Berichte aus unserem Alltag.',
                        'image_url' => $coverUrl($diaryPage),
                        'call_to_action_url' => $diaryPage?->url(),
                        'published_at' => $now,
                    ],
                    [
                        'id' => 6,
                        'title' => 'Über uns',
                        'description' => 'Verschaffe dir einen Überblick!',
                        'image_url' => $coverUrl($aboutPage),
                        'call_to_action_url' => $aboutPage?->url(),
                        'published_at' => $now,
                    ],
                    [
                        'id' => 7,
                        'title' => 'WhatsApp Community',
                        'description' => 'Tritt unserer WhatsApp Community bei und bleibe immer auf dem Laufenden!',
                        'image_url' => url('assets/svg/machmit-logo.svg'),
                        'call_to_action_url' => 'https://chat.whatsapp.com/IxjUee7gVOY3KfQhvdUsA3?mode=gi_t',
                    ],
                ];
            },
        ],
    ],
];
