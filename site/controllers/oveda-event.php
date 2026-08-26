<?php

use Kirby\Http\Remote;
use Kirby\Toolkit\Str;

require_once __DIR__ . '/events-api.php';

const MMH_OVEDA_API = 'https://oveda.de/api/v1';
const MMH_OVEDA_BASE = 'https://oveda.de';

/**
 * Cached GET against the Oveda API. Returns null on any transport or
 * decoding problem, so callers only have to check for null.
 */
function mmhOvedaGet(string $url, int $ttl = 15): array|null
{
    $cache = kirby()->cache('oveda');
    $cacheKey = 'get/' . hash('sha256', $url);
    $cached = $cache->get($cacheKey);

    if (is_array($cached) === true) {
        return $cached['data'] ?? null;
    }

    try {
        $response = Remote::get($url);

        if ($response->code() !== 200) {
            $cache->set($cacheKey, ['data' => null], 1);

            return null;
        }

        $data = $response->json();
    } catch (Throwable $e) {
        return null;
    }

    if (is_array($data) === false) {
        return null;
    }

    $cache->set($cacheKey, ['data' => $data], $ttl);

    return $data;
}

/**
 * A single event date. Only carries start/end plus an event stub, which is
 * why the full event has to be fetched separately.
 */
function mmhOvedaEventDateById(int $id): array|null
{
    return mmhOvedaGet(MMH_OVEDA_API . '/event-dates/' . $id);
}

function mmhOvedaEventById(int $id): array|null
{
    return mmhOvedaGet(MMH_OVEDA_API . '/events/' . $id);
}

function mmhOvedaPlaceById(int $id): array|null
{
    return mmhOvedaGet(MMH_OVEDA_API . '/places/' . $id, 3600);
}

/**
 * `/events/{id}` returns the place without its location, while the search
 * endpoint and `/places/{id}` both include street and coordinates. Resolve
 * the place separately whenever the location is missing.
 */
function mmhOvedaResolvePlace(array $place): array
{
    if (is_array($place['location'] ?? null) === true) {
        return $place;
    }

    $placeId = (int) ($place['id'] ?? 0);

    if ($placeId <= 0) {
        return $place;
    }

    $resolved = mmhOvedaPlaceById($placeId);

    return is_array($resolved) === true ? $resolved : $place;
}

function mmhOvedaAbsoluteUrl(string|null $path): string|null
{
    $path = trim((string) $path);

    if ($path === '') {
        return null;
    }

    return str_starts_with($path, '/') ? MMH_OVEDA_BASE . $path : $path;
}

function mmhOvedaCleanString(mixed $value): string
{
    return trim((string) ($value ?? ''));
}

/**
 * Internal detail page URL for an event date.
 */
function mmhOvedaEventUrl(int|string|null $eventDateId): string
{
    return url('events/' . $eventDateId);
}

/**
 * Assembles everything the detail template needs from the two API calls.
 */
function mmhOvedaEventDetail(int $eventDateId): array|null
{
    $eventDate = mmhOvedaEventDateById($eventDateId);

    if ($eventDate === null) {
        return null;
    }

    $eventId = (int) ($eventDate['event']['id'] ?? 0);
    $event = $eventId > 0 ? mmhOvedaEventById($eventId) : null;

    if ($event === null) {
        return null;
    }

    $timezone = new DateTimeZone(date_default_timezone_get());
    $start = new DateTimeImmutable((string) ($eventDate['start'] ?? 'now'));
    $start = $start->setTimezone($timezone);
    $end = empty($eventDate['end'])
        ? null
        : (new DateTimeImmutable((string) $eventDate['end']))->setTimezone($timezone);
    $allday = (bool) ($eventDate['allday'] ?? false);
    $now = new DateTimeImmutable('now', $timezone);
    $today = $now->format('Y-m-d');

    $place = mmhOvedaResolvePlace($event['place'] ?? []);
    $placeLocation = $place['location'] ?? [];
    $latitude = mmhOvedaCleanString($placeLocation['latitude'] ?? null);
    $longitude = mmhOvedaCleanString($placeLocation['longitude'] ?? null);

    $photo = $event['photo'] ?? [];
    $license = $photo['license'] ?? [];

    $status = mmhOvedaCleanString($event['status'] ?? 'scheduled');

    $detail = [
        'id' => (int) ($eventDate['id'] ?? $eventDateId),
        'event_id' => $eventId,
        'title' => mmhOvedaCleanString($event['name'] ?? '') ?: 'Veranstaltung',
        'description' => mmhOvedaCleanString($event['description'] ?? ''),
        'status' => $status,
        'is_cancelled' => $status === 'cancelled',
        'is_postponed' => $status === 'postponed',
        'start' => $start,
        'end' => $end,
        'allday' => $allday,
        'is_today' => $start->format('Y-m-d') === $today,
        'is_past' => ($end ?? $start) < $now,
        'date_label' => mmhOvedaGermanDate($start),
        'time_label' => mmhOvedaTimeLabel($start, $end, $allday),
        'duration_label' => mmhOvedaDurationLabel($start, $end, $allday),
        'countdown_label' => mmhOvedaCountdownLabel($start, $now),
        'categories' => mmhOvedaEventCategories(['event' => $event]),
        'tags' => array_values(array_filter(array_map(
            'trim',
            explode(',', mmhOvedaCleanString($event['tags'] ?? '')),
        ))),
        'photo' => [
            'url' => mmhOvedaAbsoluteUrl($photo['image_url'] ?? null),
            'copyright' => mmhOvedaCleanString($photo['copyright_text'] ?? null),
            'license_code' => mmhOvedaCleanString($license['code'] ?? null),
            'license_name' => mmhOvedaCleanString($license['name'] ?? null),
            'license_url' => mmhOvedaCleanString($license['url'] ?? null) ?: null,
        ],
        'place' => [
            'name' => mmhOvedaCleanString($place['name'] ?? null),
            'street' => mmhOvedaCleanString($placeLocation['street'] ?? null),
            'zip' => mmhOvedaCleanString($placeLocation['postalCode'] ?? null),
            'city' => mmhOvedaCleanString($placeLocation['city'] ?? null),
            'latitude' => $latitude !== '' ? (float) $latitude : null,
            'longitude' => $longitude !== '' ? (float) $longitude : null,
            'url' => mmhOvedaCleanString($place['url'] ?? null) ?: null,
        ],
        'organizer' => mmhOvedaContact($event['organizer'] ?? []),
        'organization' => mmhOvedaContact($event['organization'] ?? []),
        'price_info' => mmhOvedaCleanString($event['price_info'] ?? null),
        'is_free' => (bool) ($event['accessible_for_free'] ?? false),
        'ticket_link' => mmhOvedaCleanString($event['ticket_link'] ?? null) ?: null,
        'external_link' => mmhOvedaCleanString($event['external_link'] ?? null) ?: null,
        'registration_required' => (bool) ($event['registration_required'] ?? false),
        'booked_up' => (bool) ($event['booked_up'] ?? false),
        'kid_friendly' => (bool) ($event['kid_friendly'] ?? false),
        'age_from' => $event['age_from'] !== null ? (int) $event['age_from'] : null,
        'age_to' => $event['age_to'] !== null ? (int) $event['age_to'] : null,
        'attendance_mode' => mmhOvedaCleanString($event['attendance_mode'] ?? null),
        'expected_participants' => $event['expected_participants'] !== null
            ? (int) $event['expected_participants']
            : null,
        'source_url' => MMH_OVEDA_BASE . '/eventdate/' . ($eventDate['id'] ?? $eventDateId),
        'ics_url' => url('events/' . ($eventDate['id'] ?? $eventDateId) . '.ics'),
    ];

    $detail['address_lines'] = array_values(array_filter([
        $detail['place']['name'],
        $detail['place']['street'],
        trim($detail['place']['zip'] . ' ' . $detail['place']['city']),
    ]));
    $detail['maps_url'] = mmhOvedaMapsUrl($detail['place']);
    $detail['facts'] = mmhOvedaEventFacts($detail);
    $detail['other_dates'] = mmhOvedaEventOtherDates($detail);

    return $detail;
}

function mmhOvedaContact(array $contact): array
{
    $location = $contact['location'] ?? [];

    return [
        'name' => mmhOvedaCleanString($contact['name'] ?? null),
        'email' => mmhOvedaCleanString($contact['email'] ?? null) ?: null,
        'phone' => mmhOvedaCleanString($contact['phone'] ?? null) ?: null,
        'url' => mmhOvedaCleanString($contact['url'] ?? null) ?: null,
        'city' => mmhOvedaCleanString($location['city'] ?? null),
    ];
}

function mmhOvedaMapsUrl(array $place): string|null
{
    if ($place['latitude'] !== null && $place['longitude'] !== null) {
        return 'https://www.openstreetmap.org/?mlat=' . $place['latitude']
            . '&mlon=' . $place['longitude']
            . '#map=17/' . $place['latitude'] . '/' . $place['longitude'];
    }

    $query = trim(implode(', ', array_filter([
        $place['name'],
        $place['street'],
        trim($place['zip'] . ' ' . $place['city']),
    ])));

    if ($query === '') {
        return null;
    }

    return 'https://www.openstreetmap.org/search?query=' . urlencode($query);
}

function mmhOvedaGermanDate(DateTimeImmutable $date): string
{
    $weekdays = [
        1 => 'Montag',
        2 => 'Dienstag',
        3 => 'Mittwoch',
        4 => 'Donnerstag',
        5 => 'Freitag',
        6 => 'Samstag',
        7 => 'Sonntag',
    ];
    $months = [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'März',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Dezember',
    ];

    return $weekdays[(int) $date->format('N')]
        . ', ' . $date->format('j') . '. '
        . $months[(int) $date->format('n')]
        . ' ' . $date->format('Y');
}

function mmhOvedaTimeLabel(
    DateTimeImmutable $start,
    DateTimeImmutable|null $end,
    bool $allday,
): string {
    if ($allday === true) {
        return 'Ganztägig';
    }

    if ($end === null) {
        return $start->format('H:i') . ' Uhr';
    }

    // A run that crosses midnight needs the end date spelled out
    if ($end->format('Y-m-d') !== $start->format('Y-m-d')) {
        return $start->format('H:i') . ' Uhr bis '
            . $end->format('d.m.Y') . ', ' . $end->format('H:i') . ' Uhr';
    }

    return $start->format('H:i') . ' – ' . $end->format('H:i') . ' Uhr';
}

function mmhOvedaDurationLabel(
    DateTimeImmutable $start,
    DateTimeImmutable|null $end,
    bool $allday,
): string|null {
    if ($allday === true || $end === null || $end <= $start) {
        return null;
    }

    $minutes = (int) round(($end->getTimestamp() - $start->getTimestamp()) / 60);

    if ($minutes < 60) {
        return $minutes . ' Minuten';
    }

    $hours = intdiv($minutes, 60);
    $rest = $minutes % 60;

    if ($hours >= 24) {
        $days = intdiv($hours, 24);

        return $days === 1 ? 'Ein ganzer Tag' : $days . ' Tage';
    }

    $label = $hours === 1 ? '1 Stunde' : $hours . ' Stunden';

    return $rest === 0 ? $label : $label . ' ' . $rest . ' Minuten';
}

function mmhOvedaCountdownLabel(DateTimeImmutable $start, DateTimeImmutable $now): string|null
{
    $startDay = $start->setTime(0, 0);
    $today = $now->setTime(0, 0);
    $days = (int) $today->diff($startDay)->format('%r%a');

    return match (true) {
        $days === 0 => 'Heute',
        $days === 1 => 'Morgen',
        $days === 2 => 'Übermorgen',
        $days > 2 && $days <= 14 => 'In ' . $days . ' Tagen',
        default => null,
    };
}

/**
 * The icons-first fact list. Every entry that has no data is dropped, so the
 * template can simply loop over whatever is left.
 *
 * @return array<int, array{icon: string, label: string, value: string, href?: string}>
 */
function mmhOvedaEventFacts(array $detail): array
{
    $facts = [];

    $facts[] = [
        'icon' => 'calendar',
        'label' => 'Datum',
        'value' => $detail['date_label'],
    ];

    $facts[] = [
        'icon' => 'clock',
        'label' => 'Uhrzeit',
        'value' => $detail['duration_label'] !== null
            ? $detail['time_label'] . ' (' . $detail['duration_label'] . ')'
            : $detail['time_label'],
    ];

    if ($detail['address_lines'] !== []) {
        $fact = [
            'icon' => 'map-pin',
            'label' => 'Ort',
            'value' => implode(', ', $detail['address_lines']),
        ];

        if ($detail['maps_url'] !== null) {
            $fact['href'] = $detail['maps_url'];
            $fact['href_label'] = 'Auf der Karte ansehen';
        }

        $facts[] = $fact;
    }

    if ($detail['is_free'] === true) {
        $facts[] = [
            'icon' => 'ticket',
            'label' => 'Eintritt',
            'value' => 'Kostenlos',
        ];
    } elseif ($detail['price_info'] !== '') {
        $facts[] = [
            'icon' => 'ticket',
            'label' => 'Eintritt',
            'value' => $detail['price_info'],
        ];
    }

    if ($detail['organizer']['name'] !== '') {
        $fact = [
            'icon' => 'user',
            'label' => 'Veranstalter',
            'value' => $detail['organizer']['name'],
        ];

        if ($detail['organizer']['url'] !== null) {
            $fact['href'] = $detail['organizer']['url'];
            $fact['href_label'] = 'Website öffnen';
        }

        $facts[] = $fact;
    }

    if ($detail['registration_required'] === true) {
        $facts[] = [
            'icon' => 'edit',
            'label' => 'Anmeldung',
            'value' => 'Anmeldung erforderlich',
        ];
    }

    if ($detail['age_from'] !== null || $detail['age_to'] !== null) {
        $value = match (true) {
            $detail['age_from'] !== null && $detail['age_to'] !== null
                => $detail['age_from'] . ' bis ' . $detail['age_to'] . ' Jahre',
            $detail['age_from'] !== null => 'Ab ' . $detail['age_from'] . ' Jahren',
            default => 'Bis ' . $detail['age_to'] . ' Jahre',
        };

        $facts[] = [
            'icon' => 'users',
            'label' => 'Alter',
            'value' => $value,
        ];
    }

    if ($detail['kid_friendly'] === true) {
        $facts[] = [
            'icon' => 'smile',
            'label' => 'Für Kinder',
            'value' => 'Kindgerecht',
        ];
    }

    if ($detail['attendance_mode'] !== '' && $detail['attendance_mode'] !== 'offline') {
        $facts[] = [
            'icon' => 'monitor',
            'label' => 'Format',
            'value' => match ($detail['attendance_mode']) {
                'online' => 'Online-Veranstaltung',
                'mixed' => 'Vor Ort und online',
                default => ucfirst($detail['attendance_mode']),
            },
        ];
    }

    if ($detail['expected_participants'] !== null && $detail['expected_participants'] > 0) {
        $facts[] = [
            'icon' => 'users',
            'label' => 'Erwartete Gäste',
            'value' => number_format($detail['expected_participants'], 0, ',', '.'),
        ];
    }

    return $facts;
}

/**
 * Further dates of the same event, found by searching the organisation's
 * upcoming dates for the event name and keeping the matching event id.
 *
 * @return array<int, array{id: int, url: string, date_label: string, time_label: string}>
 */
function mmhOvedaEventOtherDates(array $detail, int $limit = 8): array
{
    if ($detail['title'] === '') {
        return [];
    }

    $today = (new DateTimeImmutable('today'))->format('Y-m-d');
    $page = mmhOvedaEventDatePage($today, 1, $detail['title'], 50);

    if ($page['error'] !== null) {
        return [];
    }

    $timezone = new DateTimeZone(date_default_timezone_get());
    $dates = [];

    foreach ($page['items'] as $item) {
        $itemId = (int) ($item['id'] ?? 0);

        if ((int) ($item['event']['id'] ?? 0) !== $detail['event_id']) {
            continue;
        }

        if ($itemId === $detail['id'] || $itemId === 0) {
            continue;
        }

        $start = (new DateTimeImmutable((string) ($item['start'] ?? 'now')))->setTimezone($timezone);
        $end = empty($item['end'])
            ? null
            : (new DateTimeImmutable((string) $item['end']))->setTimezone($timezone);

        $dates[] = [
            'id' => $itemId,
            'url' => mmhOvedaEventUrl($itemId),
            'date_label' => mmhOvedaGermanDate($start),
            'time_label' => mmhOvedaTimeLabel($start, $end, (bool) ($item['allday'] ?? false)),
            'sort' => $start->getTimestamp(),
        ];
    }

    usort($dates, static fn (array $a, array $b): int => $a['sort'] <=> $b['sort']);

    return array_slice($dates, 0, $limit);
}

/**
 * Minimal iCalendar payload so the date can be added to a calendar app.
 */
function mmhOvedaEventIcs(array $detail): string
{
    $escape = static fn (string $value): string => str_replace(
        ['\\', "\n", ',', ';'],
        ['\\\\', '\\n', '\\,', '\;'],
        Str::unhtml($value),
    );

    $utc = new DateTimeZone('UTC');
    $start = $detail['start']->setTimezone($utc);
    $end = ($detail['end'] ?? $detail['start']->modify('+1 hour'))->setTimezone($utc);

    $lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//MachMit!Haus Goslar//Veranstaltungen//DE',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        'UID:oveda-eventdate-' . $detail['id'] . '@mmh.goslar.de',
        'DTSTAMP:' . (new DateTimeImmutable('now', $utc))->format('Ymd\THis\Z'),
        'DTSTART:' . $start->format('Ymd\THis\Z'),
        'DTEND:' . $end->format('Ymd\THis\Z'),
        'SUMMARY:' . $escape($detail['title']),
        'URL:' . mmhOvedaEventUrl($detail['id']),
    ];

    if ($detail['description'] !== '') {
        $lines[] = 'DESCRIPTION:' . $escape(Str::short(strip_tags($detail['description']), 600, '…'));
    }

    if ($detail['address_lines'] !== []) {
        $lines[] = 'LOCATION:' . $escape(implode(', ', $detail['address_lines']));
    }

    if ($detail['is_cancelled'] === true) {
        $lines[] = 'STATUS:CANCELLED';
    }

    $lines[] = 'END:VEVENT';
    $lines[] = 'END:VCALENDAR';

    return implode("\r\n", $lines) . "\r\n";
}
