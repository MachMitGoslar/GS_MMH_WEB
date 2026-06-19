<?php

/**
 * Custom API Routes
 * These routes have higher priority and won't be intercepted by plugins
 */
require_once __DIR__ . '/../controllers/api-images.php';
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
        [
            'pattern' => 'newsletter/subscribe',
            'method' => 'POST',
            'auth' => false,
            'action' => function () {
                if (!class_exists(\GsMmh\WebPlugin\NewsletterRecipients::class)) {
                    require_once kirby()->root('plugins') . '/gs-mmh-web-plugin/NewsletterRecipients.php';
                }

                $request = kirby()->request();

                if (trim((string) $request->get('website')) !== '') {
                    return [
                        'success' => true,
                        'message' => 'Danke für deine Anmeldung.',
                    ];
                }

                if ($request->get('privacy_accepted') !== '1') {
                    return new Kirby\Cms\Response(
                        json_encode([
                            'success' => false,
                            'message' => 'Bitte akzeptiere die Datenschutzinformationen.',
                        ], JSON_UNESCAPED_UNICODE),
                        'application/json',
                        400,
                    );
                }

                try {
                    \GsMmh\WebPlugin\NewsletterRecipients::create([
                        'first_name' => $request->get('first_name'),
                        'last_name' => $request->get('last_name'),
                        'email' => $request->get('email'),
                    ]);
                } catch (\Kirby\Exception\Exception $exception) {
                    if (str_contains($exception->getMessage(), 'bereits eingetragen')) {
                        return [
                            'success' => true,
                            'message' => 'Du bist bereits für den Newsletter angemeldet.',
                        ];
                    }

                    return new Kirby\Cms\Response(
                        json_encode([
                            'success' => false,
                            'message' => $exception->getMessage(),
                        ], JSON_UNESCAPED_UNICODE),
                        'application/json',
                        400,
                    );
                }

                return [
                    'success' => true,
                    'message' => 'Danke, du bist jetzt für den Newsletter angemeldet.',
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
                        44,
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
            'action' => function ($panelId) {

                $page = page(str_replace('+', '/', $panelId));

                if (!$page || $page->intendedTemplate()->name() !== 'newsletter') {
                    return new Kirby\Cms\Response('Not found', 'text/plain', 404);
                }

                $content = mmhNewsletterMobileHtml($page);
                $filename = preg_replace('/[^a-z0-9-]+/i', '-', $page->slug()) ?: 'newsletter';

                return new Kirby\Cms\Response($content, 'text/html', 200, [
                    'Content-Disposition' => 'attachment; filename="' . strtolower($filename) . '.html"',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            },
        ],
        /**
         * Download rendered mobile Newsletter HTML
         */
        [
            'pattern' => 'newsletter/(:any).html',
            'method' => 'GET',
            'auth' => false,
            'action' => function ($slug) {

                if (!$page = page('newsletter/' . $slug)) {
                    return new Kirby\Cms\Response('Not found', 'text/plain', 404);
                }

                if ($page->intendedTemplate()->name() !== 'newsletter') {
                    return new Kirby\Cms\Response('Not found', 'text/plain', 404);
                }

                $content = mmhNewsletterMobileHtml($page);
                $filename = preg_replace('/[^a-z0-9-]+/i', '-', $page->slug()) ?: 'newsletter';

                return new Kirby\Cms\Response($content, 'text/html', 200, [
                    'Content-Disposition' => 'attachment; filename="' . strtolower($filename) . '.html"',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            },
        ],
        /**
         * Create Newsletter JPEG
         */
        [
        'pattern' => 'newsletter-cover/(:any).jpg',
        'method' => 'GET',
        'auth' => false,
        'action' => function ($slug) {
            return mmhApiCoverJpegResponse('newsletter', $slug);
        },
        ],
        [
        'pattern' => 'newsletter-cover/(:any).svg',
        'method' => 'GET',
        'auth' => false,
        'action' => function ($slug) {
            return mmhApiCoverSvgResponse('newsletter', $slug);
        },
        ],
        /**
         * Create Notes Cover
         */
        [
        'pattern' => 'notes-cover/(:any).jpg',
        'method' => 'GET',
        'auth' => false,
        'action' => function ($slug) {
            return mmhApiCoverJpegResponse('notes', $slug);
        },
        ],
        [
        'pattern' => 'notes-cover/(:any).svg',
        'method' => 'GET',
        'auth' => false,
        'action' => function ($slug) {
            return mmhApiCoverSvgResponse('notes', $slug);
        },
        ],
        [
        'pattern' => 'app-cover/(:any).jpg',
        'method' => 'GET',
        'auth' => false,
        'action' => function ($slug) {
            return mmhApiCoverJpegResponse('app', $slug);
        },
        ],
        [
        'pattern' => 'app-cover/(:any).svg',
        'method' => 'GET',
        'auth' => false,
        'action' => function ($slug) {
            return mmhApiCoverSvgResponse('app', $slug);
        },
        ],
        /**
         * Ehrentag Goslar widget entry for Goslar App
         */
        [
            'pattern' => 'ehrentag-goslar',
            'method' => 'GET',
            'auth' => false,
            'action' => function () {
                $page = page('ehrentag-goslar');
                $timestamp = $page ? $page->modified()->toTimestamp() : time();

                return [
                    'title' => 'Ehrentag - der deutschlandweite Mitmachtag',
                    'description' => 'Finde Aktionen rundum Goslar.  ',
                    'image_url' => url('assets/pngs/ehrenamt-goslar.png'),
                    'call_to_action_url' => $page?->url() ?? url('ehrentag-goslar'),
                    'published_at' => date('Y-m-d\TH:i', $timestamp),
                    'widget_type' => null,
                ];
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
                $latestNewsletter = $newsletterPage?->children()->listed()->sortBy('published', 'desc')->first();
                $latestDiary = $diaryPage?->children()->listed()->sortBy('date', 'desc')->first();

                $now = date('Y-m-d\TH:i');

                $coverUrl = function ($p) {
                    if (!$p) {
                        return null;
                    }
                    $coverFile = $p->content()->get('cover')?->toFile();

                    return mmhApiJpegImageUrl($coverFile);
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
                        'image_url' => $latestNewsletter
                            ? mmhApiCoverFileUrl('newsletter', $latestNewsletter->slug())
                            : null,
                        'call_to_action_url' => $newsletterPage?->url(),
                        'published_at' => $now,
                    ],
                    [
                        'id' => 5,
                        'title' => 'Tagebuch',
                        'description' => 'Berichte aus unserem Alltag.',
                        'image_url' => $latestDiary
                            ? mmhApiCoverFileUrl('notes', $latestDiary->slug())
                            : null,
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
                        'image_url' => mmhApiCoverFileUrl('app', 'whatsapp-community'),
                        'call_to_action_url' => 'https://chat.whatsapp.com/IxjUee7gVOY3KfQhvdUsA3?mode=gi_t',
                    ],
                ];
            },
        ],
    ],
];
