<?php

/**
 * Custom API Routes
 * These routes have higher priority and won't be intercepted by plugins
 */

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
         * Latest Update for Goslar App Kachel
         */
        [
            'pattern' => 'latest-update',
            'method' => 'GET',
            'auth' => false,
            'action' => function () {

                // Newsletter
                $newsletters = page('newsletter')
                    ?->children()
                    ->listed() ?? pages();

                // Projekte (alle Steps)
                $projektSteps = page('1_projects')
                    ?->children()
                    ->map(fn ($p) => $p->children()->listed())
                    ->flatten() ?? pages();

                // Zusammenführen
                $alleUpdates = $newsletters->merge($projektSteps);

                if ($alleUpdates->isEmpty()) {
                    return [
                        'status' => 'error',
                        'message' => 'Keine Updates gefunden',
                        'code' => 404,
                    ];
                }

                /**
                 * 🔑 Zentrale Datumslogik – IDENTISCH zu deinem Frontend
                 */
                $sortTimestamp = function ($p) {

                    // Newsletter-Felder
                    if ($p->publish_date()->isNotEmpty()) {
                        return $p->publish_date()->toTimestamp();
                    }

                    if (method_exists($p, 'published') && $p->published()) {
                        return $p->published()->toTimestamp();
                    }

                    // Projekt-Feld
                    if ($p->project_start_date()->isNotEmpty()) {
                        return $p->project_start_date()->toTimestamp();
                    }

                    // Fallback: Modified-Date
                    if ($p->modified()) {
                        return $p->modified();
                    }

                    // Letzter Fallback: Panel-Sortierung
                    return -intval($p->num());
                };

                $neuestesUpdate = $alleUpdates
                    ->sortBy($sortTimestamp, 'desc')
                    ->first();

                return [
                    'title' => $neuestesUpdate->title()->value(),
                    'type' => $neuestesUpdate->intendedTemplate()->name(),
                    'date' => date('Y-m-d', $sortTimestamp($neuestesUpdate)),
                    'url' => $neuestesUpdate->url(),
                ];
            },
        ],
    ],

];
