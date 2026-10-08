<?php

use PHPUnit\Framework\TestCase;

final class ErrorControllerTest extends TestCase
{
    private static function controller(): Closure
    {
        return require dirname(__DIR__, 2) . '/site/controllers/error.php';
    }

    public function testNavigationIsEmptyWhenThereIsNoSitemapPage(): void
    {
        // The 404 page must never fail itself: the content may not have a
        // `sitemap` page (e.g. web_content/production at the time of writing).
        $site = new Kirby\Cms\Site(['children' => [['slug' => 'home']]]);

        $data = self::controller()($site, new Kirby\Cms\Page(['slug' => 'error']), kirby());

        $this->assertCount(0, $data['navigation']);
    }

    public function testNavigationIsAPagesCollectionWithASitemapPage(): void
    {
        $site = new Kirby\Cms\Site([
            'children' => [['slug' => 'sitemap', 'content' => ['pages' => '']]],
        ]);

        $data = self::controller()($site, new Kirby\Cms\Page(['slug' => 'error']), kirby());

        $this->assertInstanceOf(Kirby\Cms\Pages::class, $data['navigation']);
    }
}
