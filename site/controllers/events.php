<?php

return function ($page) {
    $today = new DateTimeImmutable('today');
    $todayKey = $today->format('Y-m-d');
    $currentPage = max(1, (int) get('page', 1));
    $keyword = trim((string) get('keyword', ''));
    $activeCategory = trim((string) get('category', 'all')) ?: 'all';
    $selectedDay = trim((string) get('day', ''));
    $calendarOpen = get('calendar', '') === 'open';
    $calendarMonthParam = trim((string) get('calendar_month', ''));

    if ($activeCategory !== 'all' && $activeCategory !== 'free') {
        $activeCategory = 'all';
    }

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

    $buildUrl = function (array $overrides = []) use (
        $page,
        $keyword,
        $activeCategory,
        $selectedDay,
        $calendarMonthStart,
        $calendarOpen,
    ): string {
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

    $summaryChips = [
        [
            'label' => 'Alle Events',
            'count' => 0,
            'is_active' => $activeCategory === 'all' && $selectedDay === '',
            'url' => $buildUrl(['category' => null, 'day' => null, 'page' => null]),
        ],
        [
            'label' => 'Heute',
            'count' => 0,
            'is_active' => $selectedDay === $todayKey,
            'url' => $buildUrl(['day' => $todayKey, 'page' => null]),
        ],
        [
            'label' => 'Kostenlos',
            'count' => 0,
            'is_active' => $activeCategory === 'free',
            'url' => $buildUrl(['category' => 'free', 'day' => null, 'page' => null]),
        ],
    ];

    $filters = [
        [
            'slug' => 'all',
            'label' => 'Alle',
            'count' => 0,
            'is_active' => $activeCategory === 'all',
            'url' => $buildUrl(['category' => null, 'day' => null, 'page' => null]),
        ],
        [
            'slug' => 'free',
            'label' => 'Kostenlos',
            'count' => 0,
            'is_active' => $activeCategory === 'free',
            'url' => $buildUrl(['category' => 'free', 'day' => null, 'page' => null]),
        ],
    ];

    $calendarDays = [];
    for ($offset = 0; $offset < 14; $offset++) {
        $date = $today->modify('+' . $offset . ' days');
        $dateKey = $date->format('Y-m-d');

        $calendarDays[] = [
            'key' => $dateKey,
            'weekday' => $weekdayShort[(int) $date->format('w')],
            'day' => $date->format('j'),
            'month' => $monthShort[(int) $date->format('n')],
            'is_today' => $dateKey === $todayKey,
            'is_active' => $selectedDay !== '' && $selectedDay === $dateKey,
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

        $calendarGrid[] = [
            'day' => $date->format('j'),
            'key' => $dateKey,
            'url' => $buildUrl(['day' => $dateKey, 'page' => null, 'calendar' => null]),
            'is_current_month' => $date->format('Y-m') === $calendarMonthStart->format('Y-m'),
            'is_selected' => $selectedDay === $dateKey || ($selectedDay === '' && $dateKey === $todayKey),
            'is_today' => $dateKey === $todayKey,
            'has_events' => false,
        ];
    }

    $calendarTitle = ($monthShort[(int) $calendarMonthStart->format('n')] ?? $calendarMonthStart->format('M'))
        . ' '
        . $calendarMonthStart->format('Y');

    $calendarOverlay = [
        'is_open' => $calendarOpen,
        'title' => $calendarTitle,
        'prev_url' => $buildUrl([
            'calendar' => 'open',
            'calendar_month' => $calendarMonthStart->modify('-1 month')->format('Y-m'),
        ]),
        'next_url' => $buildUrl([
            'calendar' => 'open',
            'calendar_month' => $calendarMonthStart->modify('+1 month')->format('Y-m'),
        ]),
        'close_url' => $buildUrl(['calendar' => null, 'calendar_month' => null]),
        'weekdays' => $calendarWeekdayLabels,
        'days' => $calendarGrid,
    ];

    $selectedDayLabel = null;
    if ($selectedDay !== '') {
        $selectedDayDate = new DateTimeImmutable($selectedDay);
        $selectedDayLabel = $weekdayLong[(int) $selectedDayDate->format('N')]
            . ', '
            . $selectedDayDate->format('d.')
            . ' '
            . $monthShort[(int) $selectedDayDate->format('n')];
    }

    $clientEvents = [];
    $events = [];
    $eventsError = null;
    $pagination = [
        'has_prev' => $selectedDay === '' && $currentPage > 1,
        'has_next' => false,
        'prev_url' => $currentPage > 1 ? $buildUrl(['page' => $currentPage - 1]) : null,
        'next_url' => null,
    ];
    $totalResults = 0;

    return compact(
        'activeCategory',
        'buildUrl',
        'calendarDays',
        'calendarOverlay',
        'clientEvents',
        'events',
        'eventsError',
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
