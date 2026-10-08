<?php

use PHPUnit\Framework\TestCase;

final class ApiImagesTest extends TestCase
{
    public function testHexToRgb(): void
    {
        $this->assertSame([27, 77, 137], mmhApiHexToRgb('#1b4d89'));
        $this->assertSame([255, 255, 255], mmhApiHexToRgb('FFFFFF'));
        $this->assertNull(mmhApiHexToRgb('#fff'));
        $this->assertNull(mmhApiHexToRgb('#12345g'));
    }

    public function testMixRgbInterpolatesAndClampsTheAmount(): void
    {
        $this->assertSame([50, 100, 150], mmhApiMixRgb([0, 0, 0], [100, 200, 300], 0.5));
        $this->assertSame([0, 0, 0], mmhApiMixRgb([0, 0, 0], [255, 255, 255], -3));
        $this->assertSame([255, 255, 255], mmhApiMixRgb([0, 0, 0], [255, 255, 255], 7));
    }

    public function testRgbColor(): void
    {
        $this->assertSame('rgb(1,2,3)', mmhApiRgbColor([1, 2, 3]));
    }

    public function testXmlEscape(): void
    {
        $this->assertSame('a &amp; b &lt;c&gt; &quot;d&quot; &apos;e&apos;', mmhApiXmlEscape('a & b <c> "d" \'e\''));
    }

    public function testWrapSvgTextBreaksAtWordBoundariesAndKeepsTwoLines(): void
    {
        $this->assertSame(['Kurzer Titel'], mmhApiWrapSvgText('  Kurzer   Titel '));
        $this->assertSame(
            ['Ein sehr langer Titel der umbrochen', 'werden muss'],
            mmhApiWrapSvgText('Ein sehr langer Titel der umbrochen werden muss', 36),
        );
        $this->assertCount(2, mmhApiWrapSvgText(str_repeat('wort ', 60), 20), 'never more than two lines');
        $this->assertSame([], mmhApiWrapSvgText('   '));
    }

    public function testCoverFileSlugOnlyKeepsSafeCharacters(): void
    {
        $this->assertSame('note-dein-slug', mmhApiCoverFileSlug('note', 'dein-slug'));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9._-]+$/', mmhApiCoverFileSlug('note', 'ä ö/ü?x=1'));
        $this->assertStringNotContainsString('/', mmhApiCoverFileSlug('note', '../../etc/passwd'));
    }
}
