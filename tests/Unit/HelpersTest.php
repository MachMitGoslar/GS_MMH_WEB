<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    #[DataProvider('statusColors')]
    public function testProjectStatusColor(string $status, string $expected): void
    {
        $this->assertSame($expected, getProjectStatusColor($status));
    }

    public static function statusColors(): array
    {
        return [
            ['in Planung', 'planning'],
            ['in Vorbereitung', 'preparing'],
            ['aktiv', 'active'],
            ['in Auswertung', 'review'],
            ['abgeschlossen', 'done'],
            // current behaviour: unknown status yields the string 'false'
            ['unbekannt', 'false'],
        ];
    }

    public function testTimestampValueAcceptsIntsAndNumericStrings(): void
    {
        $this->assertSame(1700000000, mmhTimestampValue(1700000000));
        $this->assertSame(1700000000, mmhTimestampValue('1700000000'));
    }

    public function testTimestampValueUsesToTimestampOfObjects(): void
    {
        $field = new class () {
            public function toTimestamp(): int
            {
                return 42;
            }
        };

        $this->assertSame(42, mmhTimestampValue($field));
    }

    public function testTimestampValueFallsBackToNow(): void
    {
        $before = time();
        $value = mmhTimestampValue(null);

        $this->assertGreaterThanOrEqual($before, $value);
        $this->assertLessThanOrEqual(time(), $value);
    }

    public function testRebaseStylesheetUrlsMakesRelativeUrlsRootRelative(): void
    {
        $dir = realpath(__DIR__ . '/../../public') . '/assets/css/site/components';
        $css = '.a{background:url(../img/a.png)} .b{background:url("./b.svg")}';

        $this->assertSame(
            '.a{background:url("/assets/css/site/img/a.png")} .b{background:url("/assets/css/site/components/b.svg")}',
            mmhRebaseStylesheetUrls($css, $dir),
        );
    }

    public function testRebaseStylesheetUrlsLeavesAbsoluteReferencesAlone(): void
    {
        $dir = realpath(__DIR__ . '/../../public') . '/assets/css';
        $css = '.a{background:url(/abs.png)} .b{background:url(data:image/png;base64,AAAA)} '
            . '.c{background:url(https://example.com/x.png)} .d{background:url(#frag)}';

        $this->assertSame($css, mmhRebaseStylesheetUrls($css, $dir));
    }

    public function testRebaseStylesheetUrlsIgnoresDirectoriesOutsideTheIndexRoot(): void
    {
        $css = '.a{background:url(a.png)}';

        $this->assertSame($css, mmhRebaseStylesheetUrls($css, '/somewhere/else'));
    }

    public function testTimedContentIsVisibleWithoutDates(): void
    {
        $content = new class () {
        };

        $this->assertTrue(isTimedContentVisible($content));
    }

    protected function tearDown(): void
    {
        kirby()->impersonate(null);
    }

    private static function block(array $content): Kirby\Cms\Block
    {
        return new Kirby\Cms\Block(['type' => 'text', 'content' => $content]);
    }

    public function testBlocksAreHiddenBeforePublishAndAfterEnd(): void
    {
        $this->assertFalse(isTimedContentVisible(self::block(['publish_date' => '2099-01-01 10:00'])));
        $this->assertFalse(isTimedContentVisible(self::block(['end_date' => '2000-01-01 10:00'])));
    }

    public function testBlocksInsideTheirTimeWindowAreVisible(): void
    {
        $this->assertTrue(isTimedContentVisible(self::block(['publish_date' => '2000-01-01 10:00'])));
        $this->assertTrue(isTimedContentVisible(self::block(['end_date' => '2099-01-01 10:00'])));
        $this->assertTrue(isTimedContentVisible(self::block([
            'publish_date' => '2000-01-01 10:00',
            'end_date' => '2099-01-01 10:00',
        ])));
        $this->assertTrue(isTimedContentVisible(self::block(['publish_date' => '', 'end_date' => ''])), 'empty = no restriction');
        $this->assertTrue(isTimedContentVisible(self::block(['text' => 'no dates at all'])));
    }

    public function testLayoutsReadTheirDatesFromTheAttributes(): void
    {
        $layout = fn (array $attrs) => new Kirby\Cms\Layout(['id' => 'l', 'attrs' => $attrs, 'columns' => []]);

        $this->assertFalse(isTimedContentVisible($layout(['publish_date' => '2099-01-01 10:00'])));
        $this->assertFalse(isTimedContentVisible($layout(['end_date' => '2000-01-01 10:00'])));
        $this->assertTrue(isTimedContentVisible($layout(['publish_date' => '', 'end_date' => ''])));
    }

    public function testPagesAreHiddenOutsideTheirTimeWindow(): void
    {
        $page = fn (array $content) => new Kirby\Cms\Page(['slug' => 'p', 'content' => $content]);

        $this->assertFalse(isTimedContentVisible($page(['publish_date' => '2099-01-01 10:00'])));
        $this->assertFalse(isTimedContentVisible($page(['end_date' => '2000-01-01 10:00'])));
        $this->assertTrue(isTimedContentVisible($page(['publish_date' => '2000-01-01 10:00'])));
    }

    public function testTimesAreInterpretedInTheConfiguredTimezone(): void
    {
        // 2 hours ahead of now in Berlin is in the future whatever the server timezone is
        $berlin = new DateTimeZone('Europe/Berlin');
        $soon = (new DateTimeImmutable('now', $berlin))->modify('+2 hours')->format('Y-m-d H:i');
        $justPassed = (new DateTimeImmutable('now', $berlin))->modify('-2 hours')->format('Y-m-d H:i');

        $this->assertFalse(isTimedContentVisible(self::block(['publish_date' => $soon])));
        $this->assertTrue(isTimedContentVisible(self::block(['publish_date' => $justPassed])));
    }

    public function testAnUnparsableDateDoesNotHideContent(): void
    {
        $this->assertTrue(isTimedContentVisible(self::block(['publish_date' => 'bald'])));
    }

    public function testEditorsAndAdminsAlwaysSeeTimedContent(): void
    {
        $future = self::block(['publish_date' => '2099-01-01 10:00']);

        $this->assertFalse(isTimedContentVisible($future));

        kirby()->impersonate('kirby');

        $this->assertTrue(isTimedContentVisible($future));
    }
}
