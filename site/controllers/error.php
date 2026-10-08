<?php

use Kirby\Cms\Pages;
/**
 * Error page controller
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $navigationPages
 * returns the navigation pages for the sitemap
 *
 */

return function ($site, $page, $kirby) {
    // The 404 page must never fail itself, so a content without a `sitemap`
    // page gets an empty navigation instead of a fatal error (HTTP 500).
    $sitemap = $site->find('sitemap');
    $navigation = $sitemap ? $sitemap->pages()->toPages() : new Pages([]);

    return compact('navigation');
};
