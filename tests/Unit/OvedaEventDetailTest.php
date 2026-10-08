<?php

use PHPUnit\Framework\TestCase;

final class OvedaEventDetailTest extends TestCase
{
    private static function normalized(string $date, array $overrides = []): array
    {
        return mmhNormalizeOvedaEvent(array_replace_recursive([
            'id' => 1,
            'start' => $date . 'T10:00:00+01:00',
            'event' => [
                'name' => 'Termin',
                'accessible_for_free' => false,
                'categories' => [['name' => 'Workshop']],
            ],
        ], $overrides), '2026-03-07');
    }

    public function testMetaCountsTotalTodayFreeAndCategories(): void
    {
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $events = [
            self::normalized($today, ['id' => 1, 'event' => ['accessible_for_free' => true]]),
            self::normalized($today, ['id' => 2, 'event' => ['categories' => [['name' => 'Kinder'], ['name' => 'Workshop']]]]),
            self::normalized('2030-01-01', ['id' => 3, 'event' => ['categories' => [['name' => 'Kinder']]]]),
        ];

        $meta = mmhOvedaEventMeta($events, 40);

        $this->assertSame(
            [
                ['label' => 'Alle Events', 'count' => 40],
                ['label' => 'Heute', 'count' => 2],
                ['label' => 'Kostenlos', 'count' => 1],
            ],
            $meta['summary'],
        );
        $this->assertSame(
            [
                ['slug' => 'all', 'label' => 'Alle', 'count' => 40],
                ['slug' => 'free', 'label' => 'Kostenlos', 'count' => 1],
                // equal counts are sorted alphabetically by label
                ['slug' => 'kinder', 'label' => 'Kinder', 'count' => 2],
                ['slug' => 'workshop', 'label' => 'Workshop', 'count' => 2],
            ],
            $meta['filters'],
        );
    }

    public function testMetaSortsCategoriesByCountThenLabel(): void
    {
        $events = [
            self::normalized('2030-01-01', ['id' => 1, 'event' => ['categories' => [['name' => 'Zebra']]]]),
            self::normalized('2030-01-02', ['id' => 2, 'event' => ['categories' => [['name' => 'Alpha'], ['name' => 'Zebra']]]]),
            self::normalized('2030-01-03', ['id' => 3, 'event' => ['categories' => [['name' => 'Mitte']]]]),
        ];

        $labels = array_column(array_slice(mmhOvedaEventMeta($events, 3)['filters'], 2), 'label');

        $this->assertSame(['Zebra', 'Alpha', 'Mitte'], $labels);
    }

    private static function detail(array $overrides = []): array
    {
        return array_replace([
            'id' => 77,
            'event_id' => 5,
            'title' => 'Repair-Café, groß; fein',
            'description' => '<p>Bring <b>Geräte</b> mit.</p>',
            'start' => new DateTimeImmutable('2026-03-07 18:30', new DateTimeZone('Europe/Berlin')),
            'end' => new DateTimeImmutable('2026-03-07 20:00', new DateTimeZone('Europe/Berlin')),
            'date_label' => 'Samstag, 7. März 2026',
            'time_label' => '18:30 – 20:00 Uhr',
            'duration_label' => '1 Stunde 30 Minuten',
            'address_lines' => ['MachMit!Haus', 'Markt 7', '38640 Goslar'],
            'maps_url' => 'https://www.openstreetmap.org/search?query=x',
            'is_free' => true,
            'price_info' => '',
            'organizer' => ['name' => 'MMH', 'url' => 'https://mmh.goslar.de'],
            'registration_required' => false,
            'age_from' => null,
            'age_to' => null,
            'kid_friendly' => false,
            'attendance_mode' => 'offline',
            'expected_participants' => null,
            'is_cancelled' => false,
        ], $overrides);
    }

    public function testFactsForAFullEvent(): void
    {
        $facts = mmhOvedaEventFacts(self::detail());

        $this->assertSame(
            ['Datum', 'Uhrzeit', 'Ort', 'Eintritt', 'Veranstalter'],
            array_column($facts, 'label'),
        );
        $this->assertSame('18:30 – 20:00 Uhr (1 Stunde 30 Minuten)', $facts[1]['value']);
        $this->assertSame('MachMit!Haus, Markt 7, 38640 Goslar', $facts[2]['value']);
        $this->assertSame('Kostenlos', $facts[3]['value']);
        $this->assertSame('https://mmh.goslar.de', $facts[4]['href']);
    }

    public function testFactsDropEntriesWithoutData(): void
    {
        $facts = mmhOvedaEventFacts(self::detail([
            'duration_label' => null,
            'address_lines' => [],
            'is_free' => false,
            'organizer' => ['name' => '', 'url' => null],
        ]));

        $this->assertSame(['Datum', 'Uhrzeit'], array_column($facts, 'label'));
        $this->assertSame('18:30 – 20:00 Uhr', $facts[1]['value']);
    }

    public function testFactsPriceAgeFormatAndGuests(): void
    {
        $facts = mmhOvedaEventFacts(self::detail([
            'is_free' => false,
            'price_info' => '5 € pro Person',
            'registration_required' => true,
            'age_from' => 6,
            'age_to' => 12,
            'kid_friendly' => true,
            'attendance_mode' => 'mixed',
            'expected_participants' => 1500,
        ]));
        $byLabel = array_column($facts, 'value', 'label');

        $this->assertSame('5 € pro Person', $byLabel['Eintritt']);
        $this->assertSame('Anmeldung erforderlich', $byLabel['Anmeldung']);
        $this->assertSame('6 bis 12 Jahre', $byLabel['Alter']);
        $this->assertSame('Kindgerecht', $byLabel['Für Kinder']);
        $this->assertSame('Vor Ort und online', $byLabel['Format']);
        $this->assertSame('1.500', $byLabel['Erwartete Gäste']);
    }

    public function testFactsAgeVariants(): void
    {
        $age = fn (?int $from, ?int $to) => array_column(
            mmhOvedaEventFacts(self::detail(['age_from' => $from, 'age_to' => $to])),
            'value',
            'label',
        )['Alter'] ?? null;

        $this->assertSame('Ab 10 Jahren', $age(10, null));
        $this->assertSame('Bis 5 Jahre', $age(null, 5));
        $this->assertNull($age(null, null));
    }

    public function testIcsIsAWellFormedEscapedCalendarEntry(): void
    {
        $ics = mmhOvedaEventIcs(self::detail());
        $lines = explode("\r\n", $ics);

        $this->assertSame('BEGIN:VCALENDAR', $lines[0]);
        $this->assertSame('END:VCALENDAR', $lines[count($lines) - 2]);
        $this->assertSame('', $lines[count($lines) - 1], 'ends with CRLF');
        $this->assertContains('UID:oveda-eventdate-77@mmh.goslar.de', $lines);
        $this->assertContains('DTSTART:20260307T173000Z', $lines);
        $this->assertContains('DTEND:20260307T190000Z', $lines);
        $this->assertContains('SUMMARY:Repair-Café\\, groß\; fein', $lines);
        $this->assertContains('LOCATION:MachMit!Haus\\, Markt 7\\, 38640 Goslar', $lines);
        $this->assertContains('DESCRIPTION:Bring Geräte mit.', $lines);
        $this->assertNotContains('STATUS:CANCELLED', $lines);
    }

    public function testIcsMarksCancelledEventsAndDefaultsToOneHour(): void
    {
        $ics = mmhOvedaEventIcs(self::detail(['end' => null, 'is_cancelled' => true, 'description' => '', 'address_lines' => []]));

        $this->assertStringContainsString("STATUS:CANCELLED\r\n", $ics);
        $this->assertStringContainsString("DTEND:20260307T183000Z\r\n", $ics);
        $this->assertStringNotContainsString('DESCRIPTION:', $ics);
        $this->assertStringNotContainsString('LOCATION:', $ics);
    }
}
