<?php

use Kirby\Http\Remote;
use Kirby\Toolkit\Str;

return function ($page) {
    $today = new DateTimeImmutable('today');
    $todayKey = $today->format('Y-m-d');
    $currentPage = max(1, (int) get('page', 1));
    $keyword = trim((string) get('keyword', ''));
    $activeCategory = trim((string) get('category', 'all')) ?: 'all';
    $selectedDay = trim((string) get('day', ''));
    $calendarOpen = get('calendar', '') === 'open';
    $calendarMonthParam = trim((string) get('calendar_month', ''));

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
    $weekdayShort = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
    $weekdayLong = [
        1 => 'Montag',
        2 => 'Dienstag',
        3 => 'Mittwoch',
        4 => 'Donnerstag',
        5 => 'Freitag',
        6 => 'Samstag',
        7 => 'Sonntag',
    ];

    if ($selectedDay !== '') {
        $selectedDayDate = DateTimeImmutable::createFromFormat('Y-m-d', $selectedDay);
        if (!$selectedDayDate instanceof DateTimeImmutable) {
            $selectedDay = '';
        }
    }

    $calendarMonthDate = $selectedDay !== ''
        ? new DateTimeImmutable($selectedDay)
        : $today;
    if ($calendarMonthParam !== '') {
        $parsedCalendarMonth = DateTimeImmutable::createFromFormat('Y-m', $calendarMonthParam);
        if ($parsedCalendarMonth instanceof DateTimeImmutable) {
            $calendarMonthDate = $parsedCalendarMonth;
        }
    }
    $calendarMonthStart = $calendarMonthDate->modify('first day of this month');

    $fetchEventPage = static function (string $dateFrom, int $pageNumber = 1, string $searchKeyword = ''): array {
        $url = 'https://oveda.de/api/v1/organizations/19/event-dates/search?per_page=50&date_from=' . urlencode($dateFrom) . '&page=' . $pageNumber;

        if ($searchKeyword !== '') {
            $url .= '&keyword=' . urlencode($searchKeyword);
        }

        try {
            $response = Remote::get($url);
            $data = $response->json();

            if (!is_array($data) || isset($data['errors'])) {
                return ['items' => [], 'has_next' => false];
            }

            return [
                'items' => is_array($data['items'] ?? null) ? $data['items'] : [],
                'has_next' => (bool) ($data['has_next'] ?? false),
            ];
        } catch (Throwable $e) {
            return ['items' => [], 'has_next' => false];
        }
    };

    $fetchAllEvents = static function (string $dateFrom, string $searchKeyword = '') use ($fetchEventPage): array {
        $items = [];
        $pageNumber = 1;

        while (true) {
            $result = $fetchEventPage($dateFrom, $pageNumber, $searchKeyword);
            $items = array_merge($items, $result['items']);

            if ($result['has_next'] !== true) {
                break;
            }

            $pageNumber++;
        }

        return $items;
    };

    $extractCategories = static function (array $event): array {
        $names = [];

        foreach ($event['event']['categories'] ?? [] as $category) {
            $name = trim((string) ($category['name'] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        foreach ($event['event']['custom_categories'] ?? [] as $category) {
            $name = trim((string) ($category['name'] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        $names = array_values(array_filter(
            array_unique($names),
            static function (string $name): bool {
                $normalized = Str::lower(trim($name));

                if ($normalized === 'other') {
                    return false;
                }

                return !str_ends_with($normalized, 'sonstige');
            },
        ));

        return $names;
    };

    $categorySlug = static function (string $label): string {
        return Str::slug(Str::lower($label));
    };

    $normalizeEvent = static function (array $event) use ($extractCategories, $monthShort, $weekdayLong, $todayKey): array {
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
        $location = implode(', ', array_values(array_filter($placeBits)));
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
            'weekday_label' => $weekdayLong[(int) $start->format('N')],
            'time_label' => ($event['allday'] ?? false)
                ? 'Ganztägig'
                : $start->format('H:i') . ($end ? ' - ' . $end->format('H:i') : ''),
            'list_time_label' => ($event['allday'] ?? false)
                ? $start->format('d.m.Y') . ', ganztägig'
                : $start->format('d.m.Y, H:i') . ' Uhr',
            'location' => $location,
            'categories' => $extractCategories($event),
            'photo' => is_string($photo) && $photo !== '' ? $photo : null,
            'is_free' => (bool) ($eventData['accessible_for_free'] ?? false),
            'is_today' => $start->format('Y-m-d') === $todayKey,
            'raw' => $event,
        ];
    };

    $allUpcomingRaw = $fetchAllEvents($todayKey, $keyword);
    if ($selectedDay !== '' && $selectedDay > $todayKey) {
        $selectedDayRaw = $fetchAllEvents($selectedDay, $keyword);
        $merged = [];

        foreach (array_merge($allUpcomingRaw, $selectedDayRaw) as $event) {
            $mergeKey = (string) ($event['id'] ?? md5(json_encode($event)));
            $merged[$mergeKey] = $event;
        }

        $allUpcomingRaw = array_values($merged);
    }
    $allUpcoming = array_map($normalizeEvent, $allUpcomingRaw);

    usort(
        $allUpcoming,
        static fn (array $left, array $right) => $left['start'] <=> $right['start'],
    );

    $summaryChips = [
        [
            'label' => 'Alle Events',
            'count' => count($allUpcoming),
            'is_active' => $activeCategory === 'all' && $selectedDay === '',
            'url' => null,
        ],
        [
            'label' => 'Heute',
            'count' => count(array_filter($allUpcoming, static fn (array $event): bool => $event['is_today'])),
            'is_active' => $selectedDay === $todayKey,
            'url' => null,
        ],
        [
            'label' => 'Kostenlos',
            'count' => count(array_filter($allUpcoming, static fn (array $event): bool => $event['is_free'])),
            'is_active' => $activeCategory === 'free',
            'url' => null,
        ],
    ];

    $categoryMap = [];
    foreach ($allUpcoming as $event) {
        foreach ($event['categories'] as $categoryLabel) {
            $slug = $categorySlug($categoryLabel);
            if ($slug === '') {
                continue;
            }

            if (!isset($categoryMap[$slug])) {
                $categoryMap[$slug] = [
                    'slug' => $slug,
                    'label' => $categoryLabel,
                    'count' => 0,
                ];
            }

            $categoryMap[$slug]['count']++;
        }
    }

    uasort(
        $categoryMap,
        static function (array $left, array $right): int {
            if ($left['count'] === $right['count']) {
                return strcasecmp($left['label'], $right['label']);
            }

            return $right['count'] <=> $left['count'];
        },
    );

    if ($activeCategory !== 'all' && $activeCategory !== 'free' && !isset($categoryMap[$activeCategory])) {
        $activeCategory = 'all';
    }

    $buildUrl = function (array $overrides = []) use ($page, $keyword, $activeCategory, $selectedDay, $calendarMonthStart, $calendarOpen): string {
        $query = [
            'keyword' => $keyword !== '' ? $keyword : null,
            'category' => $activeCategory !== 'all' ? $activeCategory : null,
            'day' => $selectedDay !== '' ? $selectedDay : null,
            'calendar_month' => $calendarMonthStart->format('Y-m'),
            'calendar' => $calendarOpen ? 'open' : null,
            'page' => null,
        ];

        foreach ($overrides as $key => $value) {
            $query[$key] = $value;
        }

        $query = array_filter(
            $query,
            static fn ($value) => $value !== null && $value !== '',
        );

        $queryString = http_build_query($query);

        return $page->url() . ($queryString !== '' ? '?' . $queryString : '');
    };

    $summaryChips[0]['url'] = $buildUrl(['category' => null, 'day' => null]);
    $summaryChips[1]['url'] = $buildUrl(['day' => $todayKey]);
    $summaryChips[2]['url'] = $buildUrl(['category' => 'free', 'day' => null]);

    $filters = [
        [
            'slug' => 'all',
            'label' => 'Alle',
            'count' => count($allUpcoming),
            'is_active' => $activeCategory === 'all',
            'url' => $buildUrl(['category' => null, 'day' => null, 'page' => null]),
        ],
    ];

    foreach ($categoryMap as $category) {
        $filters[] = [
            'slug' => $category['slug'],
            'label' => $category['label'],
            'count' => $category['count'],
            'is_active' => $activeCategory === $category['slug'],
            'url' => $buildUrl(['category' => $category['slug'], 'page' => null]),
        ];
    }

    $filteredEvents = array_values(array_filter(
        $allUpcoming,
        static function (array $event) use ($activeCategory, $selectedDay, $categorySlug): bool {
            if ($activeCategory === 'free' && $event['is_free'] !== true) {
                return false;
            }

            if ($activeCategory !== 'all' && $activeCategory !== 'free') {
                $eventCategorySlugs = array_map($categorySlug, $event['categories']);
                if (!in_array($activeCategory, $eventCategorySlugs, true)) {
                    return false;
                }
            }

            if ($selectedDay !== '' && $event['date_key'] !== $selectedDay) {
                return false;
            }

            return true;
        },
    ));

    $calendarDays = [];
    for ($offset = 0; $offset < 14; $offset++) {
        $date = $today->modify('+' . $offset . ' days');
        $dateKey = $date->format('Y-m-d');
        $count = 0;

        foreach ($allUpcoming as $event) {
            $categoryMatches = true;
            if ($activeCategory === 'free') {
                $categoryMatches = $event['is_free'] === true;
            } elseif ($activeCategory !== 'all') {
                $categoryMatches = in_array($activeCategory, array_map($categorySlug, $event['categories']), true);
            }

            if ($categoryMatches && $event['date_key'] === $dateKey) {
                $count++;
            }
        }

        $isToday = $dateKey === $todayKey;
        $isActive = $selectedDay !== '' && $selectedDay === $dateKey;

        $calendarDays[] = [
            'key' => $dateKey,
            'weekday' => $weekdayShort[(int) $date->format('w')],
            'day' => $date->format('j'),
            'month' => $monthShort[(int) $date->format('n')],
            'count' => $count,
            'is_today' => $isToday,
            'is_active' => $isActive,
            'url' => $buildUrl(['day' => $dateKey, 'page' => null]),
        ];
    }

    $calendarWeekdayLabels = ['MO', 'DI', 'MI', 'DO', 'FR', 'SA', 'SO'];
    $calendarGrid = [];
    $gridStart = $calendarMonthStart->modify('monday this week');
    if ((int) $calendarMonthStart->format('N') === 1) {
        $gridStart = $calendarMonthStart;
    }

    for ($offset = 0; $offset < 35; $offset++) {
        $date = $gridStart->modify('+' . $offset . ' days');
        $dateKey = $date->format('Y-m-d');
        $hasEvents = false;
        foreach ($allUpcoming as $event) {
            if ($event['date_key'] === $dateKey) {
                $hasEvents = true;

                break;
            }
        }

        $calendarGrid[] = [
            'day' => $date->format('j'),
            'key' => $dateKey,
            'url' => $buildUrl(['day' => $dateKey, 'page' => null, 'calendar' => null]),
            'is_current_month' => $date->format('Y-m') === $calendarMonthStart->format('Y-m'),
            'is_selected' => $selectedDay === $dateKey || ($selectedDay === '' && $dateKey === $todayKey),
            'is_today' => $dateKey === $todayKey,
            'has_events' => $hasEvents,
        ];
    }

    $calendarOverlay = [
        'is_open' => $calendarOpen,
        'title' => ($monthShort[(int) $calendarMonthStart->format('n')] ?? $calendarMonthStart->format('M')) . ' ' . $calendarMonthStart->format('Y'),
        'prev_url' => $buildUrl(['calendar' => 'open', 'calendar_month' => $calendarMonthStart->modify('-1 month')->format('Y-m')]),
        'next_url' => $buildUrl(['calendar' => 'open', 'calendar_month' => $calendarMonthStart->modify('+1 month')->format('Y-m')]),
        'close_url' => $buildUrl(['calendar' => null, 'calendar_month' => null]),
        'weekdays' => $calendarWeekdayLabels,
        'days' => $calendarGrid,
    ];

    $pageSize = 12;
    $totalResults = count($filteredEvents);
    $totalPages = max(1, (int) ceil($totalResults / $pageSize));
    $currentPage = min($currentPage, $totalPages);
    $offset = ($currentPage - 1) * $pageSize;
    $events = array_slice($filteredEvents, $offset, $pageSize);

    $pagination = [
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'prev_url' => $currentPage > 1 ? $buildUrl(['page' => $currentPage - 1]) : null,
        'next_url' => $currentPage < $totalPages ? $buildUrl(['page' => $currentPage + 1]) : null,
    ];

    $selectedDayLabel = null;
    if ($selectedDay !== '') {
        $selectedDayDate = new DateTimeImmutable($selectedDay);
        $selectedDayLabel = $weekdayLong[(int) $selectedDayDate->format('N')] . ', ' . $selectedDayDate->format('d.') . ' ' . $monthShort[(int) $selectedDayDate->format('n')];
    }

    $clientEvents = array_map(
        static fn (array $event): array => [
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
        ],
        $allUpcoming,
    );

    return compact(
        'activeCategory',
        'buildUrl',
        'calendarDays',
        'calendarOverlay',
        'clientEvents',
        'events',
        'filters',
        'keyword',
        'pagination',
        'selectedDay',
        'selectedDayLabel',
        'summaryChips',
        'todayKey',
        'totalResults',
    );
};
