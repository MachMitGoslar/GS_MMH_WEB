<?php

use Kirby\Cms\Page;
use Kirby\Http\Exceptions\NextRouteException;
use Mmh\Site\Catalog;
use PHPUnit\Framework\TestCase;

final class CatalogTest extends TestCase
{
    private static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    /** Block snippets of the site; the plugins are not part of this repository (CI has none). */
    private static function snippetDirs(): array
    {
        return [self::root() . '/site/snippets/blocks'];
    }

    public function testEveryBlockTypeIsShownOrExcusedOnPurpose(): void
    {
        $types = Catalog::typesWithSnippet(self::snippetDirs());
        $shown = array_keys(Catalog::entries(new Page(['slug' => 'beispiel'])));
        $excused = array_keys(Catalog::NOT_SHOWN);

        $this->assertNotEmpty($types);
        $this->assertSame([], array_values(array_intersect($shown, $excused)), 'shown and excused at once');
        $this->assertSame(
            [],
            array_values(array_diff($types, $shown, $excused)),
            'a block type has neither a catalog entry nor a reason in Catalog::NOT_SHOWN',
        );
        $this->assertSame(
            [],
            array_values(array_diff([...$shown, ...$excused], $types)),
            'the catalog names a block type that has no snippet',
        );
    }

    public function testEntriesHaveLabelsAndContent(): void
    {
        foreach (Catalog::entries(new Page(['slug' => 'beispiel'])) as $type => $entries) {
            $this->assertNotEmpty($entries, $type);

            foreach ($entries as $entry) {
                $this->assertNotSame('', $entry['label'], $type);
                $this->assertNotEmpty($entry['content'], $type . ': ' . $entry['label']);
            }
        }
    }

    public function testPageCardsAreLeftOutWithoutASamplePage(): void
    {
        $withoutSample = array_column(Catalog::entries()['card'], 'label');
        $withSample = array_column(Catalog::entries(new Page(['slug' => 'beispiel']))['card'], 'label');

        $this->assertCount(3, $withoutSample);
        $this->assertCount(5, $withSample);
    }

    public function testEveryEntryRendersThroughTheBlockWrapper(): void
    {
        $extensions = require self::root() . '/site/plugins/mmh-site/extensions.php';
        $app = kirby()->clone([
            'roots' => [
                'snippets' => self::root() . '/site/snippets',
                'blueprints' => self::root() . '/site/blueprints',
            ],
            'blockMethods' => $extensions['blockMethods'],
            'blockModels' => $extensions['blockModels'],
            'options' => ['mmh.mapbox.token' => ''],
            'site' => ['children' => [[
                'slug' => 'beispiel',
                'num' => 1,
                'content' => ['title' => 'Beispielseite', 'headline' => 'Beispielseite'],
            ]]],
        ]);
        $sample = $app->page('beispiel');

        foreach (Catalog::entries($sample) as $type => $entries) {
            foreach ($entries as $entry) {
                $html = $app->snippet('utilities/blocks', [
                    'blocks' => Catalog::blocks($type, $entry['content'], $sample),
                ], true);

                $this->assertStringContainsString('c-blog-' . $type, $html, $type . ': ' . $entry['label']);
                $this->assertGreaterThan(100, strlen($html), $type . ': ' . $entry['label']);
            }
        }
    }

    public function testGuestsDoNotSeeTheCatalog(): void
    {
        $routes = require self::root() . '/site/plugins/mmh-site/routes.php';
        $route = array_values(array_filter($routes, fn (array $r) => $r['pattern'] === 'catalog'))[0];

        kirby()->impersonate(null);

        $this->expectException(NextRouteException::class);

        $route['action']();
    }

    public function testOnlyAdminsAndEditorsMaySeeTheCatalog(): void
    {
        $app = kirby()->clone([
            'users' => [
                ['email' => 'a@example.org', 'role' => 'admin'],
                ['email' => 'e@example.org', 'role' => 'editor'],
                ['email' => 'k@example.org', 'role' => 'nobody'],
            ],
        ]);

        $this->assertFalse(Catalog::allowedFor(null));
        $this->assertTrue(Catalog::allowedFor($app->user('a@example.org')));
        $this->assertFalse(Catalog::allowedFor($app->user('k@example.org')));
    }

    public function testTheCatalogPageIsNotIndexed(): void
    {
        $page = Catalog::page();

        $this->assertSame('catalog', $page->intendedTemplate()->name());
        $this->assertSame('noindex', $page->content()->get('robots')->value());
        $this->assertSame('Block-Katalog', $page->title()->value());
    }

    public function testTheTemplateRendersEverySampleInsideTheSiteLayout(): void
    {
        $extensions = require self::root() . '/site/plugins/mmh-site/extensions.php';
        $app = kirby()->clone([
            'roots' => [
                'snippets' => self::root() . '/site/snippets',
                'blueprints' => self::root() . '/site/blueprints',
            ],
            'templates' => $extensions['templates'],
            'blockMethods' => $extensions['blockMethods'],
            'blockModels' => $extensions['blockModels'],
            'options' => ['mmh.mapbox.token' => ''],
            'site' => ['children' => [
                ['slug' => 'home', 'num' => 1, 'content' => ['title' => 'Start']],
                ['slug' => 'projects', 'num' => 2, 'children' => [
                    ['slug' => 'eins', 'num' => 1, 'template' => 'project', 'content' => ['title' => 'Projekt Eins']],
                ]],
                // the footer links the newsletter page
                ['slug' => 'newsletter', 'num' => 3, 'content' => ['title' => 'Newsletter']],
            ]],
        ]);
        $original = Kirby\Cms\App::instance();
        Kirby\Cms\App::instance($app);

        try {
            $html = Catalog::page()->render(['groups' => Catalog::entries($app->page('projects/eins'))]);
        } finally {
            Kirby\Cms\App::instance($original);
        }

        $this->assertStringContainsString('Block-Katalog', $html);
        $this->assertSame(3, substr_count($html, 'Beispielkarte'), 'three cards with their own content');
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'Projekt Eins'), 'two cards from the sample page');
        $this->assertStringContainsString('c-blog-divider', $html);
        $this->assertStringContainsString('c-blog-testimonial', $html);
        $this->assertStringContainsString('c-blog-facts', $html);
    }
}
