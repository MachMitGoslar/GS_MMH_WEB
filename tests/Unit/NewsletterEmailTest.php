<?php

use Mmh\Site\Newsletter\EmailHtml;
use Mmh\Site\Url;
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

        $this->assertSame("<p>\u{1F4E7} Mail !</p>", EmailHtml::replaceIconsWithEmoji($html));
    }

    public function testSvgWithoutDataIconIsKept(): void
    {
        $html = '<svg viewBox="0 0 1 1"><path/></svg>';

        $this->assertSame($html, EmailHtml::replaceIconsWithEmoji($html));
    }

    public function testSiteChromeIsRemovedOnce(): void
    {
        $html = '<body><header class="h">nav</header><main>content</main><footer>foot</footer>'
            . '<script>const htmlElement = document.querySelector(":root");</script><script>keep()</script></body>';

        $this->assertSame('<body><main>content</main><script>keep()</script></body>', EmailHtml::removeSiteChrome($html));
    }

    public function testSelfContainedNewsletterDropsMapboxAndSwapsTheLogo(): void
    {
        $html = '<link href="https://api.mapbox.com/mapbox-gl-js/v3/mapbox-gl.css" rel="stylesheet">'
            . '<script src="https://api.mapbox.com/mapbox-gl-js/v3/mapbox-gl.js"></script>'
            . '<script>mapboxgl.accessToken = "x"; new mapboxgl.Map({});</script>'
            . '<img class="newsletter-logo" src="/assets/svg/RZ-RGB_MM!2_iv.svg" alt="MachMit!Haus Logo">'
            . '<div id="map" class="mb-4"></div>';

        $result = EmailHtml::prepareSelfContainedNewsletter($html);

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

        $result = EmailHtml::normalizeTimelineMarkup($html);

        $this->assertLessThan(strpos($result, 'timeline-image'), strpos($result, 'timeline-connector'));
        $this->assertLessThan(strpos($result, 'timeline-content'), strpos($result, 'timeline-image'));
    }

    public function testMainIsWrappedInAnEmailTable(): void
    {
        $result = EmailHtml::wrapMainForEmail('<main class="main"><p>x</p></main>');

        $this->assertStringStartsWith('<main class="main"><table role="presentation"', $result);
        $this->assertStringContainsString('width="600"', $result);
        $this->assertStringEndsWith('<p>x</p></td></tr></table></main>', $result);
    }

    public function testUnsubscribeLinkIsInsertedBeforeMainEndAndEscaped(): void
    {
        $result = EmailHtml::injectUnsubscribeLink('<main><p>x</p></main>', 'https://mmh.test/u?a=1&b="2"');

        $this->assertMatchesRegularExpression('!<p>x</p><section class="grid content mb-7">.*Newsletter abbestellen.*</section>\n</main>!s', $result);
        $this->assertStringContainsString('href="https://mmh.test/u?a=1&amp;b=&quot;2&quot;"', $result);
    }

    public function testUnsubscribeLinkIsAppendedWithoutMain(): void
    {
        $result = EmailHtml::injectUnsubscribeLink('<p>x</p>', 'https://mmh.test/u');

        $this->assertStringStartsWith("<p>x</p>\n<section", $result);
    }

    public function testMobileExportClassIsAddedToTheBody(): void
    {
        $this->assertSame('<body class="newsletter-mobile-export"><p>x</p>', EmailHtml::addMobileExportClass('<body><p>x</p>'));
        $this->assertSame('<body id="a" class="home newsletter-mobile-export">', EmailHtml::addMobileExportClass('<body id="a" class="home">'));
    }

    public function testInlineStyleIsAppendedToMatchingClassesOnly(): void
    {
        $html = '<p class="a b">1</p><p class="ab">2</p><p class="a" style="color:red">3</p><p>4</p>';

        $this->assertSame(
            '<p class="a b" style="margin:0">1</p><p class="ab">2</p><p class="a" style="color:red;margin:0">3</p><p>4</p>',
            EmailHtml::appendInlineStyleToClass($html, 'a', 'margin:0'),
        );
    }

    public function testInlineStyleIsAppendedToTags(): void
    {
        $this->assertSame(
            '<h1 style="margin:0">T</h1><h10>x</h10><h1 class="c" style="a:b;margin:0">U</h1>',
            EmailHtml::appendInlineStyleToTag('<h1>T</h1><h10>x</h10><h1 class="c" style="a:b">U</h1>', 'h1', 'margin:0'),
        );
    }

    public function testAbsoluteUrlLeavesAbsoluteAndEmptyUrlsAlone(): void
    {
        $this->assertSame('', Url::absolute(''));
        $this->assertSame('https://x.test/a', Url::absolute('https://x.test/a'));
        $this->assertSame('HTTP://x.test/a', Url::absolute('HTTP://x.test/a'));
    }

    public function testAbsoluteUrlResolvesRelativePathsAgainstAnAbsoluteBase(): void
    {
        $result = Url::absolute('/assets/a.png');

        $this->assertMatchesRegularExpression('!^https?://[^/]+/assets/a\.png$!', $result);
    }

    public function testDocumentUrlsBecomeAbsolute(): void
    {
        $html = EmailHtml::absoluteDocumentUrls(
            '<a href="/a">x</a><img src=\'/media/b.jpg\'><div style="background:url(/media/c.jpg)"></div>'
            . '<a href="//cdn.test/d">y</a><a href="https://x.test/e">z</a>',
        );

        $this->assertMatchesRegularExpression('!href="https?://[^/]+/a"!', $html);
        $this->assertMatchesRegularExpression('!src=\'https?://[^/]+/media/b\.jpg\'!', $html);
        $this->assertMatchesRegularExpression('!url\(https?://[^/)]+/media/c\.jpg\)!', $html);
        $this->assertStringContainsString('href="//cdn.test/d"', $html, 'protocol-relative URLs stay');
        $this->assertStringContainsString('href="https://x.test/e"', $html);
    }

    public function testModalButtonBecomesALinkToThePublicPage(): void
    {
        $page = new Kirby\Cms\Page(['slug' => 'ausgabe-3']);
        $html = EmailHtml::replaceModalButtons(
            '<button onclick="document.getElementById(\'m\').showModal()">Mehr lesen</button>',
            $page,
        );

        $this->assertStringContainsString('Mehr lesen</a>', $html);
        $this->assertMatchesRegularExpression('!<a href="https?://[^"]+/ausgabe-3"!', $html);
        $this->assertStringNotContainsString('<button', $html);
    }
}
