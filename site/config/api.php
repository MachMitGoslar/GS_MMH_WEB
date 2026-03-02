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

                $update = latestUpdate();

                if (! $update) {
                    return [
                        'status' => 'error',
                        'message' => 'Keine Updates gefunden',
                    ];
                }

                // Datum bestimmen
                $timestamp = latestUpdateTimestamp($update);

                // Newsletter vs. Projekt-Step unterscheiden
                if ($update->intendedTemplate()->name() === 'newsletter') {

                    $image_url = url('api/newsletter-cover/' . $update->slug() . '.svg');
                    $call_to_action_url = $update->url();


                } else {
                    $project = $update->parent();

                    // UUID-Cover sauber auflösen
                    $coverFile = $project->content()->get('cover')?->toFile();

                    $image_url = $coverFile
                        ? $coverFile->url()
                        : null;

                    $call_to_action_url = $project->url();

                }

                return [
                    'title' => $update->title()->value(),
                    'description' => $update->description()->isNotEmpty()
                        ? $update->description()->value()
                        : $update->text()->excerpt(160)->value(),
                    'image_url' => $image_url,
                    'call_to_action_url' => $call_to_action_url,
                    'published_at' => date('Y-m-d\TH:i', $timestamp),
                    'widget_type' => null,
                ];
            },
        ],
    ],
];
