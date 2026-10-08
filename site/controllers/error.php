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
    $navigation = $site->find('sitemap')->pages()->toPages();

    return compact('navigation');
};
