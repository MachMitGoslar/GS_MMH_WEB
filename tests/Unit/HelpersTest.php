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

    public function testTimedContentIsHiddenBeforePublishAndAfterEnd(): void
    {
        // KNOWN BUG (documented, not fixed: the cleanup keeps behaviour as is).
        // isTimedContentVisible() checks method_exists($content, 'publish_date'),
        // but Kirby blocks, layouts and pages serve fields through __call(), so
        // that is always false and timed content is always shown. See
        // docs/CLEANUP.md. Remove this skip when the check is fixed.
        $this->markTestIncomplete('Known bug: publish_date/end_date are never evaluated.');

        $future = new Kirby\Cms\Block(['type' => 'text', 'content' => ['publish_date' => '2099-01-01 10:00']]);
        $expired = new Kirby\Cms\Block(['type' => 'text', 'content' => ['end_date' => '2000-01-01 10:00']]);

        $this->assertFalse(isTimedContentVisible($future));
        $this->assertFalse(isTimedContentVisible($expired));
    }
}
