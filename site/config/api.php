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
