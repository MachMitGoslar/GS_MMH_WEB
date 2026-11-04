<?php

use Kirby\Cms\Page;
use Mmh\Site\BlockPreview;
use PHPUnit\Framework\TestCase;

final class BlockPreviewTest extends TestCase
{
    private static function boot(): void
    {
        $extensions = require dirname(__DIR__, 2) . '/site/plugins/mmh-site/extensions.php';
        kirby()->clone([
            'roots' => [
                'snippets' => dirname(__DIR__, 2) . '/site/snippets',
                'blueprints' => dirname(__DIR__, 2) . '/site/blueprints',
            ],
            'blockMethods' => $extensions['blockMethods'],
            'blockModels' => $extensions['blockModels'],
            'options' => ['mmh.mapbox.token' => ''],
            'site' => ['children' => [
                ['slug' => 'team', 'children' => [['slug' => 'anna', 'template' => 'member']]],
            ]],
        ]);
    }

    public function testOnlyBlockTypesWithASnippetAreOffered(): void
    {
        self::boot();

        $this->assertTrue(BlockPreview::isPreviewable('card'));
        $this->assertTrue(BlockPreview::isPreviewable('divider'));
        $this->assertFalse(BlockPreview::isPreviewable('nothing'));
        $this->assertFalse(BlockPreview::isPreviewable('../config'));
        $this->assertFalse(BlockPreview::isPreviewable(''));
    }

    public function testTheDocumentHoldsTheBlockInTheSiteLayoutWithTheStylesheet(): void
    {
        self::boot();

        $html = BlockPreview::document('divider', ['thickness' => 'thick'], new Page(['slug' => 'beispiel']));

        $this->assertStringStartsWith('<!doctype html>', $html);
        $this->assertStringContainsString('<link', $html);
        $this->assertStringContainsString('c-blog-divider', $html);
        $this->assertStringContainsString('class="grid content"', $html);
        $this->assertStringContainsString('zoom:' . BlockPreview::ZOOM, $html);
    }

    public function testTheDocumentIsCompleteWithoutAParentPage(): void
    {
        self::boot();

        $html = BlockPreview::document('divider', ['thickness' => 'thick']);

        $this->assertStringContainsString('</html>', $html);
    }

    public function testPanelValuesOfPageFieldsBecomeStoredIds(): void
    {
        self::boot();

        $stored = BlockPreview::storedValues('testimonial', [
            'quote' => 'Toll',
            'source' => 'member',
            'member' => [['id' => 'team/anna', 'title' => 'Anna']],
        ]);

        $this->assertSame('Toll', $stored['quote']);
        $this->assertCount(1, $stored['member']);
        $this->assertIsString($stored['member'][0]);
    }

    public function testATestimonialWithATeamMemberRenders(): void
    {
        self::boot();

        $values = [
            'quote' => 'Toll',
            'source' => 'member',
            'member' => [['id' => 'team/anna', 'title' => 'Anna']],
        ];
        $html = BlockPreview::document('testimonial', BlockPreview::storedValues('testimonial', $values));

        $this->assertStringContainsString('Toll', $html);
        $this->assertStringNotContainsString('must be of type', $html);
    }

    public function testAnAccordionIsShownUnfolded(): void
    {
        self::boot();

        $html = BlockPreview::document('accordion', ['question' => 'Warum?', 'answer' => 'Darum.']);

        $this->assertStringContainsString('<details open class="accordion-details">', $html);
        $this->assertSame(1, substr_count($html, '<details'));
    }
}
