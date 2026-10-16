<?php

namespace Mmh\Tests\Unit;

use Kirby\Cms\Block;
use PHPUnit\Framework\TestCase;

/**
 * The block "Kartenliste" turns pages into cards (utilities/card via the card
 * snippet that fits each page's template).
 */
class CardListBlockTest extends TestCase
{
    private function render(array $content): string
    {
        $app = kirby()->clone([
            'roots' => ['snippets' => dirname(__DIR__, 2) . '/site/snippets'],
            'site' => [
                'children' => [
                    ['slug' => 'beta', 'template' => 'default', 'content' => ['title' => 'Beta']],
                    ['slug' => 'alpha', 'template' => 'default', 'content' => ['title' => 'Alpha']],
                    ['slug' => 'gamma', 'template' => 'default', 'content' => ['title' => 'Gamma']],
                ],
            ],
        ]);

        $block = new Block(['type' => 'card-list', 'content' => $content, 'kirby' => $app]);

        return $app->snippet('blocks/card-list', ['block' => $block], true);
    }

    public function testRendersSelectedPagesAsCardsInOrder(): void
    {
        $html = $this->render(['source' => 'pages', 'pages' => "- beta\n- alpha", 'columns' => '2', 'title' => 'Unsere Seiten']);

        $this->assertSame(2, substr_count($html, 'data-variant="page"'));
        $this->assertStringContainsString('data-columns="2"', $html);
        $this->assertStringContainsString('Unsere Seiten', $html);
        $this->assertLessThan(strpos($html, 'Alpha'), strpos($html, 'Beta'), 'keeps the selected order');
    }

    public function testSortsByTitleAndLimits(): void
    {
        $html = $this->render(['source' => 'pages', 'pages' => "- beta\n- gamma\n- alpha", 'sortBy' => 'title', 'limit' => '2']);

        $this->assertSame(2, substr_count($html, 'data-variant="page"'));
        $this->assertStringContainsString('Alpha', $html);
        $this->assertStringContainsString('Beta', $html);
        $this->assertStringNotContainsString('Gamma', $html);
    }

    public function testRendersNothingWithoutPages(): void
    {
        $this->assertSame('', trim($this->render(['source' => 'pages', 'pages' => ''])));
    }

    public function testPicksTheCardThatFitsThePageTemplate(): void
    {
        \Kirby\Cms\Page::$models['project'] = \ProjectPage::class;

        try {
            $app = kirby()->clone([
                'roots' => ['snippets' => dirname(__DIR__, 2) . '/site/snippets'],
                'site' => [
                    'children' => [
                        ['slug' => 'projekt', 'template' => 'project', 'content' => ['title' => 'Mein Projekt', 'project_status' => 'aktiv']],
                        ['slug' => 'seite', 'template' => 'default', 'content' => ['title' => 'Eine Seite']],
                    ],
                ],
            ]);
            $block = new Block([
                'type' => 'card-list',
                'content' => ['source' => 'pages', 'pages' => "- projekt\n- seite"],
                'kirby' => $app,
            ]);

            $html = $app->snippet('blocks/card-list', ['block' => $block], true);
        } finally {
            unset(\Kirby\Cms\Page::$models['project']);
        }

        $this->assertStringContainsString('data-variant="project"', $html);
        $this->assertStringContainsString('data-variant="page"', $html);
        $this->assertStringContainsString('Mein Projekt', $html);
        $this->assertStringContainsString('<li class="c-card"', $html, 'cards are list items inside the list');
    }

    public function testFeaturedFirstHighlightsOnlyTheFirstCard(): void
    {
        $app = kirby()->clone([
            'roots' => ['snippets' => dirname(__DIR__, 2) . '/site/snippets'],
            'site' => [
                'children' => [
                    ['slug' => 'nl-eins', 'template' => 'newsletter', 'content' => ['title' => 'Eins', 'publish_date' => '2026-01-01']],
                    ['slug' => 'nl-zwei', 'template' => 'newsletter', 'content' => ['title' => 'Zwei', 'publish_date' => '2025-01-01']],
                ],
            ],
        ]);
        $render = fn (string $featured) => $app->snippet('blocks/card-list', [
            'block' => new Block([
                'type' => 'card-list',
                'content' => ['source' => 'pages', 'pages' => "- nl-eins\n- nl-zwei", 'featured' => $featured],
                'kirby' => $app,
            ]),
        ], true);

        $this->assertSame(1, substr_count($render('first'), 'newsletter-item--featured'));
        $this->assertSame(2, substr_count($render('all'), 'newsletter-item--featured'));
        $this->assertSame(0, substr_count($render('none'), 'newsletter-item--featured'));
    }
}
