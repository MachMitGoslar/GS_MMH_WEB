<?php

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Page;
use Mmh\Site\Blocks\CardBlock;
use PHPUnit\Framework\TestCase;

final class CardBlockTest extends TestCase
{
    private ?App $original = null;

    private array $models = [];

    protected function setUp(): void
    {
        $this->original = App::instance();
        $this->models = Page::$models;

        new App([
            'roots' => [
                'index' => sys_get_temp_dir() . '/mmh-card-phpunit',
                'base' => sys_get_temp_dir() . '/mmh-card-phpunit',
                'content' => sys_get_temp_dir() . '/mmh-card-phpunit/content',
            ],
            'urls' => ['index' => 'https://mmh.test'],
            'blockModels' => ['card' => CardBlock::class],
            // like the site: pages go through the page models, which return a File from cover()
            'pageModels' => ['default' => DefaultPage::class],
            'site' => [
                'children' => [
                    [
                        'slug' => 'ohne-cover',
                        'num' => 2,
                        'files' => [['filename' => 'a-bild.jpg'], ['filename' => 'z-cover.jpg']],
                        'content' => ['title' => 'Ohne Cover'],
                    ],
                    [
                        'slug' => 'ziel',
                        'num' => 1,
                        'files' => [['filename' => 'a-bild.jpg'], ['filename' => 'z-cover.jpg']],
                        'content' => [
                            'title' => 'Zielseite',
                            'headline' => 'Die Überschrift',
                            'subheadline' => 'Untertitel',
                            'text' => 'Text der Seite',
                            'cover' => '- z-cover.jpg',
                        ],
                    ],
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        // Other tests rely on the app of tests/bootstrap.php
        App::instance($this->original);
        Page::$models = $this->models;
    }

    private static function card(array $content): Block
    {
        return Block::factory(['type' => 'card', 'content' => $content, 'parent' => page('ziel')]);
    }

    public function testTheModelIsUsedForCardBlocks(): void
    {
        $this->assertInstanceOf(CardBlock::class, self::card([]));
        $this->assertNotInstanceOf(CardBlock::class, Block::factory(['type' => 'text', 'content' => []]));
    }

    public function testAPageCardTakesEverythingFromThePage(): void
    {
        $card = self::card(['cardType' => 'page', 'page' => '- ziel', 'linkMode' => 'header']);

        $this->assertInstanceOf(Page::class, $card->cardPage());
        $this->assertSame('https://mmh.test/ziel', $card->link());
        $this->assertSame('header', $card->linkStyle());
        $this->assertSame('Die Überschrift', $card->headline()->value());
        $this->assertSame('Untertitel', $card->subheadline()->value());
        $this->assertSame('Text der Seite', $card->body()->value());
        $this->assertSame('z-cover.jpg', $card->image()?->filename());
    }

    public function testAPageCardWithoutCoverFallsBackToTheFirstImage(): void
    {
        $card = self::card(['cardType' => 'page', 'page' => '- ohne-cover']);

        $this->assertSame('a-bild.jpg', $card->image()?->filename());
    }

    public function testAManualCardUsesItsOwnFields(): void
    {
        $card = self::card([
            'cardType' => 'manual',
            'headline' => 'Eigener Titel',
            'subheadline' => 'Eigener Untertitel',
            'description_content' => '[]',
            'link' => 'https://example.org/x',
            'linkMode' => 'button',
        ]);

        $this->assertNull($card->cardPage());
        $this->assertSame('https://example.org/x', $card->link());
        $this->assertSame('button', $card->linkStyle());
        $this->assertSame('Eigener Titel', $card->headline()->value());
        $this->assertNotNull($card->body());
        $this->assertNull($card->image(), 'no file chosen');
    }

    public function testWithoutATargetThereIsNoLink(): void
    {
        $this->assertSame('none', self::card(['cardType' => 'manual', 'linkMode' => 'button'])->linkStyle());
        $this->assertSame('', self::card(['cardType' => 'manual', 'linkMode' => 'button'])->link());
    }

    public function testUnknownLinkModesCountAsNone(): void
    {
        $this->assertSame('none', self::card(['cardType' => 'page', 'page' => '- ziel', 'linkMode' => 'x'])->linkStyle());
        $this->assertSame('none', self::card(['cardType' => 'page', 'page' => '- ziel'])->linkStyle());
    }

    public function testAPageCardWithoutAPageShowsNoText(): void
    {
        $card = self::card(['cardType' => 'page']);

        $this->assertNull($card->cardPage());
        $this->assertNull($card->body());
    }

    public function testAPageWithoutModelGivesAFieldAndTheCardFallsBackToTheFirstImage(): void
    {
        // Known quirk, kept from the snippet: without a page model cover() is a Field, and
        // method_exists($field, 'toFile') is false (field methods are magic), so the cover is skipped.
        Kirby\Cms\Page::$models = []; // the registry is static and survives new App()

        $plain = new App([
            'roots' => ['index' => sys_get_temp_dir() . '/mmh-card-phpunit', 'base' => sys_get_temp_dir() . '/mmh-card-phpunit'],
            'urls' => ['index' => 'https://mmh.test'],
            'blockModels' => ['card' => CardBlock::class],
            'site' => ['children' => [[
                'slug' => 'ziel',
                'num' => 1,
                'files' => [['filename' => 'a-bild.jpg'], ['filename' => 'z-cover.jpg']],
                'content' => ['cover' => '- z-cover.jpg'],
            ]]],
        ]);

        $card = Block::factory(['type' => 'card', 'content' => ['cardType' => 'page', 'page' => '- ziel'], 'parent' => $plain->page('ziel')]);

        $this->assertSame('a-bild.jpg', $card->image()?->filename());
    }
}
