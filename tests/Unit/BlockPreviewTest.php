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
    }

    public function testTheDocumentIsCompleteWithoutAParentPage(): void
    {
        self::boot();

        $html = BlockPreview::document('divider', ['thickness' => 'thick']);

        $this->assertStringContainsString('</html>', $html);
    }
}
