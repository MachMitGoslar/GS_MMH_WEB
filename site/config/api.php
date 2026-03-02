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
         * Create Newsletter Image
         */
    [
        'pattern' => 'newsletter-cover/(:any).svg',
        'method' => 'GET',
        'auth' => false,
        'action' => function ($slug) {

            if (! $page = page('newsletter/' . $slug)) {
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

                if (! $data = latestUpdateData()) {
                    return [
                        'status' => 'error',
                        'message' => 'Keine Updates gefunden',
                    ];
                }

                return $data;
            },
        ],
        [
            'pattern' => 'highlights',
            'method' => 'GET',
            'auth' => false,
            'action' => function () {

                // Dynamisches Update holen (Newsletter oder Projekt-Step)
                $dynamic = latestUpdateData();

                if ($dynamic) {
                    $dynamic['id'] = 2;              // feste ID
                    unset($dynamic['widget_type']);  // entfernen
                }

                // Seiten holen
                $homePage = page('home');
                $projectsPage = page('projects');
                $newsletterPage = page('newsletter');
                $diaryPage = page('notes');
                $aboutPage = page('uber-uns');

                $now = date('Y-m-d\TH:i');

                // Helper-Funktion: Cover-URL wie beim latest-update
                $coverUrl = function ($p) {
                    if (! $p) {
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

                    $dynamic, // Slot 2 = Latest Update mit Cover

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
                ];
            },
        ],
    ],
];
