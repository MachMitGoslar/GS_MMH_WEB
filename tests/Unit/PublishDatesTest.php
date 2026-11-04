<?php

use Mmh\Site\PublishDates;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PublishDatesTest extends TestCase
{
    public function testAnEndBeforeTheStartIsRejected(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Das Enddatum darf nicht vor dem Veröffentlichungsdatum liegen.');

        PublishDates::assertOrder(['publish_date' => '2026-05-02 10:00', 'end_date' => '2026-05-01 10:00']);
    }

    public static function validRanges(): array
    {
        return [
            'end after start' => [['publish_date' => '2026-05-01 10:00', 'end_date' => '2026-05-02 10:00']],
            'same moment' => [['publish_date' => '2026-05-01 10:00', 'end_date' => '2026-05-01 10:00']],
            'only start' => [['publish_date' => '2026-05-01 10:00']],
            'only end' => [['end_date' => '2026-05-01 10:00']],
            'empty start' => [['publish_date' => '', 'end_date' => '2026-05-01 10:00']],
            'unparseable start' => [['publish_date' => 'bald', 'end_date' => '2020-01-01']],
            'no dates' => [['title' => 'ohne Daten']],
            'nothing' => [[]],
        ];
    }

    #[DataProvider('validRanges')]
    public function testValidOrIncompleteRangesPass(array $values): void
    {
        PublishDates::assertOrder($values);

        $this->addToAssertionCount(1);
    }

    public function testThePluginRegistersTheHook(): void
    {
        $extensions = require dirname(__DIR__, 2) . '/site/plugins/mmh-site/extensions.php';
        $hook = $extensions['hooks']['page.update:before'];

        $this->expectException(Exception::class);

        $hook(new Kirby\Cms\Page(['slug' => 'x']), ['publish_date' => '2026-02-01', 'end_date' => '2026-01-01'], []);
    }
}
