<?php

use PHPUnit\Framework\TestCase;

/**
 * The newsletter e-mail export rewrites the rendered page with regular
 * expressions. These tests pin the transformations so a template change that
 * breaks the export shows up here.
 */
final class NewsletterEmailTest extends TestCase
{
    public function testIconsAreReplacedByEmojiAndUnknownIconsAreDropped(): void
    {
        $html = '<p><svg class="i" data-icon="mail" viewBox="0 0 1 1"><path d="x"/></svg> Mail '
            . '<svg data-icon="does-not-exist"><path/></svg>!</p>';

        $this->assertSame("<p>\u{1F4E7} Mail !</p>", mmhNewsletterReplaceIconsWithEmoji($html));
    }

    public function testSvgWithoutDataIconIsKept(): void
    {
        $html = '<svg viewBox="0 0 1 1"><path/></svg>';

        $this->assertSame($html, mmhNewsletterReplaceIconsWithEmoji($html));
    }

    public function testSiteChromeIsRemovedOnce(): void
    {
        $html = '<body><header class="h">nav</header><main>content</main><footer>foot</footer>'
            . '<script>const htmlElement = document.querySelector(":root");</script><script>keep()</script></body>';

        $this->assertSame('<body><main>content</main><script>keep()</script></body>', mmhNewsletterRemoveSiteChrome($html));
    }

    public function testSelfContainedNewsletterDropsMapboxAndSwapsTheLogo(): void
    {
        $html = '<link href="https://api.mapbox.com/mapbox-gl-js/v3/mapbox-gl.css" rel="stylesheet">'
            . '<script src="https://api.mapbox.com/mapbox-gl-js/v3/mapbox-gl.js"></script>'
            . '<script>mapboxgl.accessToken = "x"; new mapboxgl.Map({});</script>'
            . '<img class="newsletter-logo" src="/assets/svg/RZ-RGB_MM!2_iv.svg" alt="MachMit!Haus Logo">'
            . '<div id="map" class="mb-4"></div>';

        $result = mmhNewsletterPrepareSelfContainedNewsletter($html);

        $this->assertStringNotContainsString('mapbox', $result);
        $this->assertStringContainsString(
            '<img class="newsletter-logo" src="/assets/generated/mmh-logo-white.png" alt="MachMit!Haus Logo" width="180" height="180">',
            $result,
        );
        $this->assertStringContainsString('<div id="map" class="mb-4 newsletter-static-map">', $result);
        $this->assertStringContainsString('Markt 7, 38640 Goslar', $result);
    }

    public function testTimelineItemsAreReorderedConnectorImageContent(): void
    {
        $html = '<div class="timeline-item timeline-item--left"> <div class="timeline-item__container"> '
            . '<div class="timeline-content"><p>text</p></div> </div> '
            . '<div class="timeline-image">img</div> '
            . '<div class="timeline-connector"></div>';

        $result = mmhNewsletterNormalizeTimelineMarkup($html);

        $this->assertLessThan(strpos($result, 'timeline-image'), strpos($result, 'timeline-connector'));
        $this->assertLessThan(strpos($result, 'timeline-content'), strpos($result, 'timeline-image'));
    }

    public function testMainIsWrappedInAnEmailTable(): void
    {
        $result = mmhNewsletterWrapMainForEmail('<main class="main"><p>x</p></main>');

        $this->assertStringStartsWith('<main class="main"><table role="presentation"', $result);
        $this->assertStringContainsString('width="600"', $result);
        $this->assertStringEndsWith('<p>x</p></td></tr></table></main>', $result);
    }

    public function testUnsubscribeLinkIsInsertedBeforeMainEndAndEscaped(): void
    {
        $result = mmhNewsletterInjectUnsubscribeLink('<main><p>x</p></main>', 'https://mmh.test/u?a=1&b="2"');

        $this->assertMatchesRegularExpression('!<p>x</p><section class="grid content mb-7">.*Newsletter abbestellen.*</section>\n</main>!s', $result);
        $this->assertStringContainsString('href="https://mmh.test/u?a=1&amp;b=&quot;2&quot;"', $result);
    }

    public function testUnsubscribeLinkIsAppendedWithoutMain(): void
    {
        $result = mmhNewsletterInjectUnsubscribeLink('<p>x</p>', 'https://mmh.test/u');

        $this->assertStringStartsWith("<p>x</p>\n<section", $result);
    }

    public function testMobileExportClassIsAddedToTheBody(): void
    {
        $this->assertSame('<body class="newsletter-mobile-export"><p>x</p>', mmhNewsletterAddMobileExportClass('<body><p>x</p>'));
        $this->assertSame('<body id="a" class="home newsletter-mobile-export">', mmhNewsletterAddMobileExportClass('<body id="a" class="home">'));
    }

    public function testInlineStyleIsAppendedToMatchingClassesOnly(): void
    {
        $html = '<p class="a b">1</p><p class="ab">2</p><p class="a" style="color:red">3</p><p>4</p>';

        $this->assertSame(
            '<p class="a b" style="margin:0">1</p><p class="ab">2</p><p class="a" style="color:red;margin:0">3</p><p>4</p>',
            mmhNewsletterAppendInlineStyleToClass($html, 'a', 'margin:0'),
        );
    }

    public function testInlineStyleIsAppendedToTags(): void
    {
        $this->assertSame(
            '<h1 style="margin:0">T</h1><h10>x</h10><h1 class="c" style="a:b;margin:0">U</h1>',
            mmhNewsletterAppendInlineStyleToTag('<h1>T</h1><h10>x</h10><h1 class="c" style="a:b">U</h1>', 'h1', 'margin:0'),
        );
    }

    public function testAbsoluteUrlLeavesAbsoluteAndEmptyUrlsAlone(): void
    {
        $this->assertSame('', mmhAbsoluteUrl(''));
        $this->assertSame('https://x.test/a', mmhAbsoluteUrl('https://x.test/a'));
        $this->assertSame('HTTP://x.test/a', mmhAbsoluteUrl('HTTP://x.test/a'));
    }

    public function testAbsoluteUrlResolvesRelativePathsAgainstAnAbsoluteBase(): void
    {
        $result = mmhAbsoluteUrl('/assets/a.png');

        $this->assertMatchesRegularExpression('!^https?://[^/]+/assets/a\.png$!', $result);
    }
}
