<?php

/**
 * Nextcloud CalDAV integration for room bookings.
 */

use Kirby\Cms\App;
use Kirby\Http\Remote;

function ncConfig(string $key, mixed $default = null): mixed
{
    return App::instance()->option('nextcloud.' . $key, $default);
}

function ncCalendarUrl(string $calendarUri): string
{
    $baseUrl = rtrim((string) ncConfig('calendar_url'), '/');
    $calendarUri = trim($calendarUri, '/');

    return $baseUrl . '/' . rawurlencode($calendarUri) . '/';
}

function ncRequest(string $method, string $url, array $headers = [], mixed $data = null)
{
    $username = (string) ncConfig('username');
    $password = (string) ncConfig('password');

    $requestHeaders = array_merge([
        'Authorization: Basic ' . base64_encode($username . ':' . $password),
        'Content-Type: application/xml; charset=utf-8',
    ], $headers);

    return Remote::request($url, [
        'method' => $method,
        'headers' => $requestHeaders,
        'data' => $data,
    ]);
}

function ncEscapeText(string $value): string
{
    $value = str_replace("\\", "\\\\", $value);
    $value = str_replace(["\r\n", "\r", "\n"], "\\n", $value);
    $value = str_replace([";", ","], ["\\;", "\\,"], $value);

    return $value;
}

function ncFoldIcsLine(string $line): string
{
    if (strlen($line) <= 75) {
        return $line;
    }

    $folded = '';
    while (strlen($line) > 75) {
        $folded .= substr($line, 0, 75) . "\r\n ";
        $line = substr($line, 75);
    }

    return $folded . $line;
}

function ncBuildIcsEvent(
    string $uid,
    string $summary,
    string $description,
    \DateTimeInterface $start,
    \DateTimeInterface $end,
    ?string $location = null,
): string {
    $utc = new \DateTimeZone('UTC');
    $now = (new \DateTimeImmutable('now', $utc))->format('Ymd\THis\Z');

    $lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//MachMitHaus//Room Booking//DE',
        'CALSCALE:GREGORIAN',
        'BEGIN:VEVENT',
        'UID:' . $uid,
        'DTSTAMP:' . $now,
        'CREATED:' . $now,
        'LAST-MODIFIED:' . $now,
        'SUMMARY:' . ncEscapeText($summary),
        'DESCRIPTION:' . ncEscapeText($description),
        'DTSTART;TZID=Europe/Berlin:' . $start->format('Ymd\THis'),
        'DTEND;TZID=Europe/Berlin:' . $end->format('Ymd\THis'),
    ];

    if ($location) {
        $lines[] = 'LOCATION:' . ncEscapeText($location);
    }

    $lines[] = 'END:VEVENT';
    $lines[] = 'END:VCALENDAR';

    return implode("\r\n", array_map('ncFoldIcsLine', $lines)) . "\r\n";
}

function ncCreateCalendarEvent(
    string $calendarUri,
    string $summary,
    string $description,
    \DateTimeInterface $start,
    \DateTimeInterface $end,
    ?string $location = null,
): array {
    if (!ncConfig('calendar_url') || !ncConfig('username') || !ncConfig('password')) {
        return [
            'success' => false,
            'message' => 'Nextcloud calendar is not configured',
        ];
    }

    $uid = bin2hex(random_bytes(16)) . '@machmit.haus';
    $eventUrl = ncCalendarUrl($calendarUri) . $uid . '.ics';
    $ics = ncBuildIcsEvent($uid, $summary, $description, $start, $end, $location);

    $response = ncRequest('PUT', $eventUrl, [
        'Content-Type: text/calendar; charset=utf-8',
    ], $ics);

    if (in_array($response->code(), [200, 201, 204], true)) {
        return [
            'success' => true,
            'eventId' => $uid,
            'href' => $eventUrl,
        ];
    }

    return [
        'success' => false,
        'message' => trim($response->content()) ?: 'Nextcloud CalDAV request failed',
        'code' => $response->code(),
    ];
}

function ncBookingEventData($bookingRequest): array
{
    $requesterName = $bookingRequest->requester_name()->value();
    $requesterOrg = $bookingRequest->requester_organization()->value();
    $purpose = $bookingRequest->purpose()->value();
    $attendees = $bookingRequest->expected_attendees()->value();
    $specialReqs = $bookingRequest->special_requirements()->value();

    $description = "Buchung: {$requesterName}";
    if ($requesterOrg) {
        $description .= " ({$requesterOrg})";
    }
    $description .= "\n\nZweck: {$purpose}";
    $description .= "\nTeilnehmer: {$attendees} Personen";
    if ($specialReqs) {
        $description .= "\n\nBesondere Anforderungen:\n{$specialReqs}";
    }

    return [
        'summary' => $requesterOrg ?: $requesterName,
        'description' => $description,
    ];
}

function ncCreateBookingCalendarEvents($bookingRequest): array
{
    $results = [];
    $berlin = new \DateTimeZone('Europe/Berlin');
    $eventData = ncBookingEventData($bookingRequest);
    $rooms = $bookingRequest->requested_rooms()->toPages();

    $requestDate = $bookingRequest->request_date()->value();
    $timeStart = $bookingRequest->request_time_start()->value();
    $timeEnd = $bookingRequest->request_time_end()->value();

    $dates = [$requestDate];
    if ($bookingRequest->is_recurring()->toBool() && $bookingRequest->recurrence_end_date()->isNotEmpty()) {
        $interval = match ($bookingRequest->recurrence_pattern()->value()) {
            'biweekly' => new \DateInterval('P2W'),
            'monthly' => new \DateInterval('P1M'),
            default => new \DateInterval('P1W'),
        };
        $current = new \DateTimeImmutable($requestDate, $berlin);
        $endDate = new \DateTimeImmutable($bookingRequest->recurrence_end_date()->value(), $berlin);

        while (($current = $current->add($interval)) <= $endDate) {
            $dates[] = $current->format('Y-m-d');
        }
    }

    foreach ($rooms as $room) {
        $calendarUri = $room->nextcloud_calendar_uri()->or($room->nextcloud_resource_email())->value();

        if (!$calendarUri) {
            $results[] = [
                'room' => $room->title()->value(),
                'success' => false,
                'message' => 'No Nextcloud calendar URI configured for this room',
            ];
            continue;
        }

        foreach ($dates as $date) {
            $start = new \DateTimeImmutable($date . ' ' . $timeStart, $berlin);
            $end = new \DateTimeImmutable($date . ' ' . $timeEnd, $berlin);
            $result = ncCreateCalendarEvent(
                $calendarUri,
                $eventData['summary'],
                $eventData['description'],
                $start,
                $end,
                $room->title()->value(),
            );
            $result['room'] = $room->title()->value();
            $result['date'] = $date;
            $results[] = $result;
        }
    }

    return $results;
}

function ncRoomBusySlots(
    string $calendarUri,
    \DateTimeInterface $rangeStart,
    \DateTimeInterface $rangeEnd,
    bool $includeTentative = true,
): array {
    if (!ncConfig('calendar_url') || !ncConfig('username') || !ncConfig('password')) {
        return [];
    }

    $utc = new \DateTimeZone('UTC');
    $startUtc = (clone $rangeStart)->setTimezone($utc)->format('Ymd\THis\Z');
    $endUtc = (clone $rangeEnd)->setTimezone($utc)->format('Ymd\THis\Z');

    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<c:calendar-query xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
  <d:prop>
    <c:calendar-data/>
  </d:prop>
  <c:filter>
    <c:comp-filter name="VCALENDAR">
      <c:comp-filter name="VEVENT">
        <c:time-range start="{$startUtc}" end="{$endUtc}"/>
      </c:comp-filter>
    </c:comp-filter>
  </c:filter>
</c:calendar-query>
XML;

    $response = ncRequest('REPORT', ncCalendarUrl($calendarUri), [
        'Depth: 1',
    ], $xml);

    if ($response->code() !== 207) {
        error_log('Nextcloud availability lookup failed for ' . $calendarUri . ': HTTP ' . $response->code());

        return [];
    }

    return ncBusySlotsFromMultistatus($response->content(), $includeTentative);
}

function ncBusySlotsFromMultistatus(string $xml, bool $includeTentative): array
{
    $document = @simplexml_load_string($xml);
    if (!$document) {
        return [];
    }

    $document->registerXPathNamespace('d', 'DAV:');
    $document->registerXPathNamespace('cal', 'urn:ietf:params:xml:ns:caldav');

    $slots = [];
    foreach ($document->xpath('//cal:calendar-data') ?: [] as $calendarData) {
        foreach (ncBusySlotsFromIcs((string) $calendarData, $includeTentative) as $slot) {
            $slots[] = $slot;
        }
    }

    usort($slots, fn ($a, $b) => strcmp($a['start'], $b['start']));

    return $slots;
}

function ncBusySlotsFromIcs(string $ics, bool $includeTentative): array
{
    $events = [];
    $lines = preg_split('/\R/', str_replace("\r\n ", '', $ics));
    $event = null;

    foreach ($lines as $line) {
        if ($line === 'BEGIN:VEVENT') {
            $event = [];
            continue;
        }

        if ($line === 'END:VEVENT') {
            if ($event !== null) {
                $status = strtoupper($event['STATUS'] ?? 'CONFIRMED');
                if ($status !== 'CANCELLED' && ($includeTentative || $status !== 'TENTATIVE')) {
                    $start = ncParseIcsDate($event['DTSTART'] ?? '');
                    $end = ncParseIcsDate($event['DTEND'] ?? '');
                    if ($start && $end) {
                        $events[] = [
                            'start' => $start->format(DATE_ATOM),
                            'end' => $end->format(DATE_ATOM),
                            'summary' => $event['SUMMARY'] ?? '',
                        ];
                    }
                }
            }
            $event = null;
            continue;
        }

        if ($event === null || !str_contains($line, ':')) {
            continue;
        }

        [$name, $value] = explode(':', $line, 2);
        $name = strtoupper(strtok($name, ';'));
        if (in_array($name, ['DTSTART', 'DTEND', 'SUMMARY', 'STATUS'], true)) {
            $event[$name] = $value;
        }
    }

    return $events;
}

function ncParseIcsDate(string $value): ?\DateTimeImmutable
{
    if (!$value) {
        return null;
    }

    $timezone = str_ends_with($value, 'Z') ? new \DateTimeZone('UTC') : new \DateTimeZone('Europe/Berlin');
    $format = str_contains($value, 'T') ? 'Ymd\THis' : 'Ymd';
    $value = rtrim($value, 'Z');
    $date = \DateTimeImmutable::createFromFormat($format, $value, $timezone);

    return $date ?: null;
}
