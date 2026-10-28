<?php

/**
 * Routes of the website itself: version, redirects of old project URLs, the
 * Oveda event pages, the newsletter feed and the "not allowed" page.
 * (The app routes under /app/ are still in site/config/routes.php.)
 */

use Kirby\Cms\Page;
use Kirby\Cms\Response;
use Kirby\Http\Exceptions\NextRouteException;
use Kirby\Toolkit\Escape;
use Mmh\Site\Catalog;
use Mmh\Site\MergedProjects;
use Mmh\Site\Oveda\EventDetail;
use Mmh\Site\Oveda\Events;

return [
    /**
     * Version of the running website, for support ("which version is on
     * staging?"). Only the version, nothing about content or setup.
     */
    [
        'pattern' => 'version.json',
        'action' => function () {
            return Response::json(['version' => mmhVersion()]);
        },
    ],

    /**
    * HOTFIX
    * 301 für alte Linksstruktur
    */
    [
        'pattern' => '/stipendien',
        'action' => function () {
            go('projects/03-machmit-stipendien', 301);
        },
    ],

    /**
     * 301 für zusammengeführte Projekte. Greift unter beiden Wurzeln,
     * weil Projekte je nach Status unter projects/ oder project-archive/ lagen.
     */
    [
        'pattern' => ['projects/(:any)', 'project-archive/(:any)'],
        'action' => function (string $slug) {
            if (($target = MergedProjects::target($slug)) !== null) {
                go($target, 301);
            }

            throw new NextRouteException();
        },
    ],
    [
        // iCalendar download for a single Oveda event date
        'pattern' => 'events/(:num).ics',
        'method' => 'GET',
        'action' => function (string $eventDateId) {
            $detail = EventDetail::detail((int) $eventDateId);

            if ($detail === null) {
                throw new NextRouteException();
            }

            return new Response(EventDetail::ics($detail), 'text/calendar', 200, [
                'Content-Disposition' => 'attachment; filename="veranstaltung-' . $detail['id'] . '.ics"',
            ]);
        },
    ],
    [
        // Detail view for a single Oveda event date
        'pattern' => 'events/(:num)',
        'method' => 'GET',
        'action' => function (string $eventDateId) {
            $detail = EventDetail::detail((int) $eventDateId);

            if ($detail === null) {
                throw new NextRouteException();
            }

            $parent = site()->find('events');

            $page = Kirby\Cms\Page::factory([
                'slug' => (string) $detail['id'],
                'template' => 'event',
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
                Events::apiPayload(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            );

            return new Response(
                $payload,
                'application/json',
            );
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

    /***** NOT ALLOWED RESPONSE FOR DEBUGGING SITES */
    [
        'pattern' => '/not-allowed',
        'action' => function () {
            $page = Page::factory([
                'slug' => 'not-allowed',
                'template' => 'special',
                'content' => [
                    'content_data' => '<div class="grid-item" data-span="1/1">
                                        <h1> 403 -  Keine Berechtigung </h1>
                                       </div> 
                                       <div class="grid-item" data-span="1/1"> 
                                        <p> Du bist nicht berechtigt diese Seite anzuzeigen </p>
                                        </div>',
                ],
            ]);

            $response = new Response(
                $page->render(),
                'text/html',
                403,
            );

            return $response;
        },
    ],

    /**
     * Block catalog: every block type in every variant, for editors only
     * (guests get the normal 404). Not linked anywhere and not indexed.
     */
    [
        'pattern' => 'catalog',
        'action' => function () {
            if (!Catalog::allowedFor(kirby()->user())) {
                throw new NextRouteException();
            }

            $sample = page('projects')?->children()->listed()->first();

            return Catalog::page()->render(['groups' => Catalog::entries($sample)]);
        },
    ],

    /**
     * Sitemap and robots.txt (indexability comes from `$page->seo()`).
     */
    [
        'pattern' => 'sitemap.xml',
        'action' => function () {
            $pages = site()->index()->filter(
                fn (Page $page) => $page->seo()->isIndexable(),
            );

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

            foreach ($pages as $page) {
                $xml .= '  <url>' . PHP_EOL;
                $xml .= '    <loc>' . Escape::html($page->url()) . '</loc>' . PHP_EOL;

                if ($modified = $page->modified('c')) {
                    $xml .= '    <lastmod>' . $modified . '</lastmod>' . PHP_EOL;
                }

                $xml .= '  </url>' . PHP_EOL;
            }

            $xml .= '</urlset>';

            return new Response($xml, 'application/xml');
        },
    ],
    [
        'pattern' => 'robots.txt',
        'action' => function () {
            $lines = [
                'User-agent: *',
                'Disallow: /panel',
                'Disallow: /signage',
                '',
                'Sitemap: ' . site()->url() . '/sitemap.xml',
            ];

            return new Response(implode(PHP_EOL, $lines) . PHP_EOL, 'text/plain');
        },
    ],
];
