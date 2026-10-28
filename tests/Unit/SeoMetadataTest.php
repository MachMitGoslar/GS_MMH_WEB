<?php

use Mmh\Site\SeoMetadata;
use Kirby\Cms\Page;
use PHPUnit\Framework\TestCase;

class SeoMetadataTest extends TestCase
{
    public function testPageTitleFallsBackToTitle(): void
    {
        $page = new Page(['slug' => 'about', 'content' => ['title' => ' Über uns ']]);

        $this->assertSame('Über uns', (new SeoMetadata($page))->pageTitle());
    }

    public function testSeoTitleWinsOverTitle(): void
    {
        $page = new Page(['slug' => 'about', 'content' => ['title' => 'Über uns', 'seo_title' => 'Das Haus']]);

        $this->assertSame('Das Haus', (new SeoMetadata($page))->pageTitle());
    }

    public function testTitleAppendsSiteName(): void
    {
        $page = new Page(['slug' => 'about', 'content' => ['title' => 'Über uns']]);

        $this->assertStringStartsWith('Über uns | ', (new SeoMetadata($page))->title());
    }

    public function testWithoutPageOnlyTheSiteNameRemains(): void
    {
        $seo = new SeoMetadata(null);

        $this->assertSame('', $seo->pageTitle());
        $this->assertSame($seo->siteTitle(), $seo->title());
    }

    public function testPlainPageIsAWebsiteAndIndexable(): void
    {
        $seo = new SeoMetadata(new Page(['slug' => 'about']));

        $this->assertSame('website', $seo->type());
        $this->assertTrue($seo->isIndexable());
    }
}
