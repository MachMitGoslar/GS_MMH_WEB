<?php

namespace Mmh\Tests\Unit;

use Kirby\Cms\Block;
use PHPUnit\Framework\TestCase;

/**
 * The editor block "Fakten" renders through utilities/facts and utilities/fact
 * (the same look as event facts and contact details).
 */
class FactsBlockTest extends TestCase
{
    private function render(array $content): string
    {
        $app = kirby()->clone([
            'roots' => [
                'snippets' => dirname(__DIR__, 2) . '/site/snippets',
                'blueprints' => dirname(__DIR__, 2) . '/site/blueprints',
            ],
            // options outlive a clone, so the token is reset explicitly
            'options' => ['mmh.mapbox.token' => ''],
        ]);

        $block = new Block(['type' => 'facts', 'content' => $content, 'kirby' => $app]);

        return $app->snippet('blocks/facts', ['block' => $block], true);
    }

    public function testRendersItemsWithLabelValueAndLink(): void
    {
        $html = $this->render([
            'columns' => '1',
            'surface' => 'plain',
            'items' => [
                ['icon' => 'mail', 'label' => 'E-Mail', 'value' => 'info@example.org', 'link' => 'mailto:info@example.org'],
                ['icon' => 'map-pin', 'label' => 'Ort', 'value' => 'Markt 7', 'link' => ''],
            ],
        ]);

        $this->assertStringContainsString('class="c-facts"', $html);
        $this->assertStringContainsString('data-columns="1"', $html);
        $this->assertStringContainsString('data-surface="plain"', $html);
        $this->assertSame(2, substr_count($html, 'class="c-fact"'));
        $this->assertStringContainsString('<a class="c-fact__value" href="mailto:info@example.org">info@example.org</a>', $html);
        $this->assertStringContainsString('<span class="c-fact__value">Markt 7</span>', $html);
        $this->assertStringContainsString('c-fact__label', $html);
    }

    public function testMapBesideTheFactsRendersAGlance(): void
    {
        $app = kirby()->clone([
            'roots' => [
                'snippets' => dirname(__DIR__, 2) . '/site/snippets',
                'blueprints' => dirname(__DIR__, 2) . '/site/blueprints',
            ],
            'options' => ['mmh.mapbox.token' => 'pk.test'],
        ]);
        $block = new Block([
            'type' => 'facts',
            'content' => [
                'map' => 'true',
                'lat' => '51.906169',
                'lng' => '10.428',
                'mapLabel' => 'MachMit!Haus',
                'items' => [['icon' => 'map-pin', 'label' => 'Ort', 'value' => 'Markt 7', 'link' => '']],
            ],
            'kirby' => $app,
        ]);

        $html = $app->snippet('blocks/facts', ['block' => $block], true);

        $this->assertStringContainsString('class="c-glance"', $html);
        $this->assertStringContainsString('data-has-map="true"', $html);
        $this->assertStringContainsString('data-lat="51.906169"', $html);
        $this->assertStringContainsString('data-label="MachMit!Haus"', $html);
        $this->assertStringContainsString('<span class="c-fact__value">Markt 7</span>', $html);
        $this->assertStringContainsString('mapbox-gl.js', $html);
    }

    public function testMapWithoutTokenFallsBackToPlainFacts(): void
    {
        $html = $this->render([
            'map' => 'true',
            'lat' => '51.9',
            'lng' => '10.4',
            'items' => [['icon' => 'map-pin', 'label' => 'Ort', 'value' => 'Markt 7', 'link' => '']],
        ]);

        $this->assertStringNotContainsString('c-glance', $html);
        $this->assertStringContainsString('class="c-facts"', $html);
    }

    public function testRendersNothingWithoutItems(): void
    {
        $this->assertSame('', trim($this->render(['items' => []])));
    }

    public function testBlockWrapperCarriesThemeAndVariant(): void
    {
        $app = kirby()->clone([
            'roots' => ['snippets' => dirname(__DIR__, 2) . '/site/snippets'],
        ]);
        $blocks = new \Kirby\Cms\Blocks([
            new Block(['type' => 'divider', 'content' => ['theme' => 'dark', 'variant' => 'vertical'], 'kirby' => $app]),
            new Block(['type' => 'divider', 'content' => ['theme' => 'default', 'variant' => 'auto'], 'kirby' => $app]),
            new Block(['type' => 'divider', 'content' => [], 'kirby' => $app]),
        ]);

        $html = $app->snippet('utilities/blocks', ['blocks' => $blocks], true);

        $this->assertSame(1, substr_count($html, 'data-theme="dark"'));
        $this->assertSame(1, substr_count($html, 'data-variant="vertical"'));
        $this->assertSame(1, substr_count($html, 'data-theme='), 'default and empty set nothing');
        $this->assertSame(1, substr_count($html, 'data-variant='), 'auto and empty set nothing');
    }

    public function testHeroBlockRendersItsImageBand(): void
    {
        $app = kirby()->clone(['roots' => ['snippets' => dirname(__DIR__, 2) . '/site/snippets']]);
        $html = fn (array $content) => $app->snippet('blocks/hero', [
            'block' => new Block(['type' => 'hero', 'content' => $content, 'kirby' => $app]),
        ], true);

        $this->assertSame('', trim($html([])), 'without an image the block renders nothing');
    }
}
