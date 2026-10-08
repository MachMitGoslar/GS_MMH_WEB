<?php

use PHPUnit\Framework\TestCase;

final class OvedaEventTest extends TestCase
{
    private static function event(array $overrides = []): array
    {
        return array_replace_recursive([
            'id' => 123,
            'start' => '2026-03-07T18:30:00+01:00',
            'end' => '2026-03-07T20:00:00+01:00',
            'allday' => false,
            'event' => [
                'name' => '  Repair-Café ',
                'description' => '<p>Bring <b>kaputte</b> Geräte mit.</p>',
                'accessible_for_free' => true,
                'photo' => ['image_url' => '/uploads/cafe.jpg'],
                'place' => [
                    'name' => 'MachMit!Haus',
                    'location' => ['street' => 'Markt 7', 'city' => 'Goslar'],
                ],
                'categories' => [['name' => 'Workshop'], ['name' => 'Sonstige']],
                'custom_categories' => [['name' => 'Workshop'], ['name' => 'Other'], ['name' => 'Kinder']],
            ],
        ], $overrides);
    }

    public function testNormalizeBuildsDisplayFields(): void
    {
        $e = mmhNormalizeOvedaEvent(self::event(), '2026-03-07');

        $this->assertSame('Repair-Café', $e['title']);
        $this->assertSame('Bring kaputte Geräte mit.', $e['description']);
        $this->assertSame('2026-03-07', $e['date_key']);
        $this->assertSame('07', $e['date_badge_day']);
        $this->assertSame('Mär', $e['date_badge_month']);
        $this->assertSame('18:30 - 20:00', $e['time_label']);
        $this->assertSame('07.03.2026, 18:30 Uhr', $e['list_time_label']);
        $this->assertSame('MachMit!Haus, Markt 7, Goslar', $e['location']);
        $this->assertSame('https://oveda.de/uploads/cafe.jpg', $e['photo']);
        $this->assertSame('https://oveda.de/eventdate/123', $e['source_url']);
        $this->assertSame('https://mmh.test/events/123', $e['url']);
        $this->assertTrue($e['is_free']);
        $this->assertTrue($e['is_today']);
    }

    public function testNormalizeMarksOtherDaysAsNotToday(): void
    {
        $e = mmhNormalizeOvedaEvent(self::event(), '2026-03-08');

        $this->assertFalse($e['is_today']);
    }

    public function testNormalizeHandlesAllDayEventsAndMissingData(): void
    {
        $e = mmhNormalizeOvedaEvent([
            'id' => 5,
            'start' => '2026-12-24T00:00:00+01:00',
            'allday' => true,
        ], '2026-01-01');

        $this->assertSame('Ganztägig', $e['time_label']);
        $this->assertSame('24.12.2026, ganztägig', $e['list_time_label']);
        $this->assertSame('Termin', $e['title']);
        $this->assertSame('', $e['location']);
        $this->assertNull($e['photo']);
        $this->assertFalse($e['is_free']);
        $this->assertSame('Dez', $e['date_badge_month']);
    }

    public function testNormalizeKeepsAbsolutePhotoUrls(): void
    {
        $e = mmhNormalizeOvedaEvent(self::event([
            'event' => ['photo' => ['image_url' => 'https://cdn.example/x.jpg']],
        ]), '2026-03-07');

        $this->assertSame('https://cdn.example/x.jpg', $e['photo']);
    }

    public function testCategoriesAreDeduplicatedAndSonstigeAndOtherAreDropped(): void
    {
        $this->assertSame(['Workshop', 'Kinder'], mmhOvedaEventCategories(self::event()));
    }

    public function testCategorySlug(): void
    {
        $this->assertSame('kunst-kultur', mmhOvedaCategorySlug('Kunst & Kultur'));
        $this->assertSame('familie-kinder', mmhOvedaCategorySlug('Familie & Kinder'));
    }

    public function testAbsoluteUrl(): void
    {
        $this->assertNull(mmhOvedaAbsoluteUrl(null));
        $this->assertNull(mmhOvedaAbsoluteUrl('   '));
        $this->assertSame('https://oveda.de/a.jpg', mmhOvedaAbsoluteUrl('/a.jpg'));
        $this->assertSame('https://x.test/a.jpg', mmhOvedaAbsoluteUrl('https://x.test/a.jpg'));
    }

    public function testGermanDate(): void
    {
        $this->assertSame(
            'Samstag, 7. März 2026',
            mmhOvedaGermanDate(new DateTimeImmutable('2026-03-07 12:00')),
        );
    }

    public function testTimeLabel(): void
    {
        $start = new DateTimeImmutable('2026-03-07 18:30');

        $this->assertSame('Ganztägig', mmhOvedaTimeLabel($start, null, true));
        $this->assertSame('18:30 Uhr', mmhOvedaTimeLabel($start, null, false));
        $this->assertSame(
            '18:30 – 20:00 Uhr',
            mmhOvedaTimeLabel($start, new DateTimeImmutable('2026-03-07 20:00'), false),
        );
        $this->assertSame(
            '18:30 Uhr bis 08.03.2026, 01:00 Uhr',
            mmhOvedaTimeLabel($start, new DateTimeImmutable('2026-03-08 01:00'), false),
        );
    }

    public function testDurationLabel(): void
    {
        $start = new DateTimeImmutable('2026-03-07 10:00');
        $at = fn (string $end) => mmhOvedaDurationLabel($start, new DateTimeImmutable($end), false);

        $this->assertNull(mmhOvedaDurationLabel($start, null, false));
        $this->assertNull(mmhOvedaDurationLabel($start, new DateTimeImmutable('2026-03-07 12:00'), true));
        $this->assertNull($at('2026-03-07 09:00'), 'end before start');
        $this->assertSame('45 Minuten', $at('2026-03-07 10:45'));
        $this->assertSame('1 Stunde', $at('2026-03-07 11:00'));
        $this->assertSame('2 Stunden 30 Minuten', $at('2026-03-07 12:30'));
        $this->assertSame('Ein ganzer Tag', $at('2026-03-08 10:00'));
        $this->assertSame('3 Tage', $at('2026-03-10 10:00'));
    }

    public function testCountdownLabel(): void
    {
        $now = new DateTimeImmutable('2026-03-07 15:00');
        $label = fn (string $start) => mmhOvedaCountdownLabel(new DateTimeImmutable($start), $now);

        $this->assertSame('Heute', $label('2026-03-07 20:00'));
        $this->assertSame('Morgen', $label('2026-03-08 09:00'));
        $this->assertSame('Übermorgen', $label('2026-03-09 09:00'));
        $this->assertSame('In 5 Tagen', $label('2026-03-12 09:00'));
        $this->assertSame('In 14 Tagen', $label('2026-03-21 09:00'));
        $this->assertNull($label('2026-03-22 09:00'));
        $this->assertNull($label('2026-03-01 09:00'), 'past events get no countdown');
    }
}
