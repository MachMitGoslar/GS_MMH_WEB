<?php

use Kirby\Http\Remote;
use Kirby\Toolkit\Str;

/**
 * @return array{
 *   items: array,
 *   has_next: bool,
 *   total: int|null,
 *   page: int,
 *   per_page: int,
 *   error: string|null,
 *   rate_limit: array
 * }
 */
function mmhOvedaEventDatePage(
    string $dateFrom,
    int $pageNumber = 1,
    string $searchKeyword = '',
    int $perPage = 12,
): array {
    $pageNumber = max(1, $pageNumber);
    $perPage = max(1, min(50, $perPage));

    $url = 'https://oveda.de/api/v1/organizations/19/event-dates/search'
        . '?per_page=' . $perPage
        . '&date_from=' . urlencode($dateFrom)
        . '&page=' . $pageNumber;

    if ($searchKeyword !== '') {
        $url .= '&keyword=' . urlencode($searchKeyword);
    }

    $cache = kirby()->cache('oveda');
    $cacheKey = 'event-dates/' . hash('sha256', $url);
    $cached = $cache->get($cacheKey);

    if (is_array($cached) === true) {
        return $cached;
    }

    $result = mmhFetchOvedaEventDatePage($url, $pageNumber, $perPage);
    $cache->set($cacheKey, $result, $result['error'] === null ? 60 : 5);

    return $result;
}

/**
 * @return array{
 *   items: array,
 *   total: int,
 *   complete: bool,
 *   error: string|null
 * }
 */
function mmhOvedaEventDateOverview(string $dateFrom, string $searchKeyword = ''): array
{
    $items = [];
    $total = 0;
    $complete = true;
    $error = null;

    for ($pageNumber = 1; $pageNumber <= 20; $pageNumber++) {
        $page = mmhOvedaEventDatePage($dateFrom, $pageNumber, $searchKeyword, 50);

        if ($pageNumber === 1) {
            $total = $page['total'] ?? 0;
        }

        if ($page['error'] !== null) {
            $error = $page['error'];
            $complete = false;

            break;
        }

        $items = array_merge($items, $page['items']);

        if ($page['has_next'] === false) {
            break;
        }

        if ($pageNumber === 20) {
            $complete = false;
        }
    }

    return [
        'items' => $items,
        'total' => $total > 0 ? $total : count($items),
        'complete' => $complete,
        'error' => $error,
    ];
}

/**
 * @return array{
 *   items: array,
 *   has_next: bool,
 *   total: int|null,
 *   page: int,
 *   per_page: int,
 *   error: string|null,
 *   rate_limit: array
 * }
 */
function mmhEmptyOvedaEventDatePage(
    int $pageNumber,
    int $perPage,
    string|null $error = null,
    array $rateLimit = [],
): array {
    return [
        'items' => [],
        'has_next' => false,
        'total' => 0,
        'page' => $pageNumber,
        'per_page' => $perPage,
        'error' => $error,
        'rate_limit' => $rateLimit,
    ];
}

function mmhFetchOvedaEventDatePage(string $url, int $pageNumber, int $perPage): array
{
    try {
        $response = Remote::get($url);
        $data = $response->json();
        $rateLimit = mmhOvedaRateLimitHeaders($response);

        if (is_array($data) === false || is_array($data['items'] ?? null) === false) {
            $message = (string) ($data['name'] ?? $data['message'] ?? 'Invalid response');
            if ($response->code()) {
                $message .= ' (' . $response->code() . ')';
            }

            return mmhEmptyOvedaEventDatePage($pageNumber, $perPage, $message, $rateLimit);
        }
    } catch (Throwable $e) {
        return mmhEmptyOvedaEventDatePage($pageNumber, $perPage, $e->getMessage());
    }

    return [
        'items' => array_values($data['items']),
        'has_next' => (bool) ($data['has_next'] ?? false),
        'total' => isset($data['total']) ? (int) $data['total'] : null,
        'page' => $pageNumber,
        'per_page' => $perPage,
        'error' => null,
        'rate_limit' => $rateLimit,
    ];
}

function mmhOvedaRateLimitHeaders(Remote $response): array
{
    $headers = [];

    foreach ($response->headers() as $key => $value) {
        if (str_starts_with(strtolower($key), 'x-ratelimit-')) {
            $headers[$key] = trim((string) $value);
        }
    }

    return $headers;
}

/**
 * @return array<int, array>
 */
function mmhNormalizeOvedaEvents(array $events, string|null $todayKey = null): array
{
    $todayKey ??= date('Y-m-d');

    return array_values(array_map(
        static fn (array $event): array => mmhNormalizeOvedaEvent($event, $todayKey),
        $events,
    ));
}

function mmhNormalizeOvedaEvent(array $event, string|null $todayKey = null): array
{
    $monthShort = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mär',
        4 => 'Apr',
        5 => 'Mai',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Aug',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Dez',
    ];
    $todayKey ??= date('Y-m-d');
    $start = isset($event['start']) ? new DateTimeImmutable((string) $event['start']) : new DateTimeImmutable();
    $end = !empty($event['end']) ? new DateTimeImmutable((string) $event['end']) : null;
    $eventData = $event['event'] ?? [];
    $place = $eventData['place'] ?? [];
    $placeLocation = $place['location'] ?? [];
    $placeBits = [
        trim((string) ($place['name'] ?? '')),
        trim((string) ($placeLocation['street'] ?? '')),
        trim((string) ($placeLocation['city'] ?? '')),
    ];
    $photo = $eventData['photo']['image_url'] ?? null;

    if (is_string($photo) && $photo !== '' && str_starts_with($photo, '/')) {
        $photo = 'https://oveda.de' . $photo;
    }

    return [
        'id' => $event['id'] ?? null,
        'url' => 'https://oveda.de/eventdate/' . ($event['id'] ?? ''),
        'title' => trim((string) ($eventData['name'] ?? 'Termin')),
        'description' => trim(strip_tags((string) ($eventData['description'] ?? ''))),
        'start' => $start,
        'end' => $end,
        'date_key' => $start->format('Y-m-d'),
        'date_badge_day' => $start->format('d'),
        'date_badge_month' => $monthShort[(int) $start->format('n')],
        'time_label' => ($event['allday'] ?? false)
            ? 'Ganztägig'
            : $start->format('H:i') . ($end ? ' - ' . $end->format('H:i') : ''),
        'list_time_label' => ($event['allday'] ?? false)
            ? $start->format('d.m.Y') . ', ganztägig'
            : $start->format('d.m.Y, H:i') . ' Uhr',
        'location' => implode(', ', array_values(array_filter($placeBits))),
        'categories' => mmhOvedaEventCategories($event),
        'photo' => is_string($photo) && $photo !== '' ? $photo : null,
        'is_free' => (bool) ($eventData['accessible_for_free'] ?? false),
        'is_today' => $start->format('Y-m-d') === $todayKey,
        'raw' => $event,
    ];
}

/**
 * @return array<int, string>
 */
function mmhOvedaEventCategories(array $event): array
{
    $eventData = $event['event'] ?? [];
    $names = [];

    foreach ($eventData['categories'] ?? [] as $category) {
        $name = trim((string) ($category['name'] ?? ''));
        if ($name !== '') {
            $names[] = $name;
        }
    }

    foreach ($eventData['custom_categories'] ?? [] as $category) {
        $name = trim((string) ($category['name'] ?? ''));
        if ($name !== '') {
            $names[] = $name;
        }
    }

    return array_values(array_filter(
        array_unique($names),
        static function (string $name): bool {
            $normalized = Str::lower(trim($name));

            if ($normalized === 'other') {
                return false;
            }

            return !str_ends_with($normalized, 'sonstige');
        },
    ));
}

function mmhOvedaCategorySlug(string $label): string
{
    return Str::slug(Str::lower($label));
}

function mmhOvedaEventClientPayload(array $event): array
{
    return [
        'id' => $event['id'],
        'url' => $event['url'],
        'title' => $event['title'],
        'description' => $event['description'],
        'date_key' => $event['date_key'],
        'date_badge_day' => $event['date_badge_day'],
        'date_badge_month' => $event['date_badge_month'],
        'list_time_label' => $event['list_time_label'],
        'time_label' => $event['time_label'],
        'location' => $event['location'],
        'categories' => $event['categories'],
        'is_free' => $event['is_free'],
        'is_today' => $event['is_today'],
    ];
}

/**
 * @return array{
 *   summary: array<int, array{label: string, count: int}>,
 *   filters: array<int, array{slug: string, label: string, count: int}>
 * }
 */
function mmhOvedaEventMeta(array $events, int $total): array
{
    $todayKey = (new DateTimeImmutable('today'))->format('Y-m-d');
    $todayCount = 0;
    $freeCount = 0;
    $categories = [];

    foreach ($events as $event) {
        if ($event['date_key'] === $todayKey) {
            $todayCount++;
        }

        if ($event['is_free'] === true) {
            $freeCount++;
        }

        foreach ($event['categories'] as $category) {
            $slug = mmhOvedaCategorySlug($category);

            if ($slug === '') {
                continue;
            }

            $categories[$slug] ??= [
                'slug' => $slug,
                'label' => $category,
                'count' => 0,
            ];
            $categories[$slug]['count']++;
        }
    }

    usort(
        $categories,
        static fn (array $a, array $b): int => $b['count'] <=> $a['count'] ?: strcmp($a['label'], $b['label']),
    );

    return [
        'summary' => [
            [
                'label' => 'Alle Events',
                'count' => $total,
            ],
            [
                'label' => 'Heute',
                'count' => $todayCount,
            ],
            [
                'label' => 'Kostenlos',
                'count' => $freeCount,
            ],
        ],
        'filters' => array_values(array_merge(
            [
                [
                    'slug' => 'all',
                    'label' => 'Alle',
                    'count' => $total,
                ],
                [
                    'slug' => 'free',
                    'label' => 'Kostenlos',
                    'count' => $freeCount,
                ],
            ],
            $categories,
        )),
    ];
}

function mmhEventsApiPayload(): array
{
    $todayKey = (new DateTimeImmutable('today'))->format('Y-m-d');
    $page = max(1, (int) get('page', 1));
    $perPage = max(1, min(50, (int) get('per_page', 12)));
    $keyword = trim((string) get('keyword', ''));
    $selectedDay = trim((string) get('day', ''));
    $activeCategory = trim((string) get('category', 'all')) ?: 'all';

    if ($selectedDay !== '') {
        $selectedDayDate = DateTimeImmutable::createFromFormat('Y-m-d', $selectedDay);
        if (!$selectedDayDate instanceof DateTimeImmutable) {
            $selectedDay = '';
        }
    }

    $dateFrom = $selectedDay !== '' ? $selectedDay : $todayKey;
    $apiPage = $selectedDay !== '' ? 1 : $page;
    $apiPerPage = $selectedDay !== '' ? 50 : $perPage;
    $eventPage = mmhOvedaEventDatePage($dateFrom, $apiPage, $keyword, $apiPerPage);
    $events = mmhNormalizeOvedaEvents($eventPage['items'], $todayKey);
    $overview = mmhOvedaEventDateOverview($todayKey, $keyword);
    $overviewEvents = mmhNormalizeOvedaEvents($overview['items'], $todayKey);
    $meta = mmhOvedaEventMeta($overviewEvents, $overview['total']);

    if ($selectedDay !== '') {
        $events = array_values(array_filter(
            $events,
            static fn (array $event): bool => $event['date_key'] === $selectedDay,
        ));
    }

    $availableFilterSlugs = array_column($meta['filters'], 'slug');

    if (
        $activeCategory !== 'all' &&
        $activeCategory !== 'free' &&
        in_array($activeCategory, $availableFilterSlugs, true) === false
    ) {
        $activeCategory = 'all';
    }

    if ($selectedDay === '' && $activeCategory !== 'all') {
        $events = $overviewEvents;
    }

    $events = array_values(array_filter(
        $events,
        static function (array $event) use ($activeCategory): bool {
            if ($activeCategory === 'free') {
                return $event['is_free'] === true;
            }

            if ($activeCategory !== 'all') {
                return in_array(
                    $activeCategory,
                    array_map('mmhOvedaCategorySlug', $event['categories']),
                    true,
                );
            }

            return true;
        },
    ));

    $filteredTotal = count($events);

    if ($selectedDay === '' && $activeCategory !== 'all') {
        $offset = ($page - 1) * $perPage;
        $events = array_slice($events, $offset, $perPage);
    }

    $paginationTotal = $selectedDay !== '' || $activeCategory !== 'all'
        ? $filteredTotal
        : $eventPage['total'];

    return [
        'events' => array_map('mmhOvedaEventClientPayload', $events),
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'has_prev' => $selectedDay === '' && $page > 1,
            'has_next' => $selectedDay === '' && (
                $activeCategory === 'all'
                    ? $eventPage['has_next'] === true
                    : $page * $perPage < $filteredTotal
            ),
            'total' => $paginationTotal,
        ],
        'summary' => $meta['summary'],
        'filters' => $meta['filters'],
        'meta_complete' => $overview['complete'],
        'error' => $eventPage['error'],
        'rate_limit' => $eventPage['rate_limit'],
    ];
}
