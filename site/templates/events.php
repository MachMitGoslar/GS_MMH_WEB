<?php
/**
 * @var Kirby\Cms\Site $site
 * @var Kirby\Cms\Page $page
 */
?>

<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main
    class="main main--events"
    data-events-page
    data-active-category="<?= esc($activeCategory) ?>"
    data-today="<?= esc($todayKey) ?>"
    data-selected-day="<?= esc($selectedDay) ?>"
>
    <div class="mb-4">
        <?=snippet('sections/hero')?>
    </div>

    <section class="grid content mb-7">
        <div class="grid-item p-tb-4" data-span="1/1">
            <div class="events-toolbar mb-3">
                <div class="events-toolbar__stats">
                    <?php foreach ($summaryChips as $chip) : ?>
                        <a
                            class="events-toolbar-chip"
                            data-active="<?= $chip['is_active'] ? 'true' : 'false' ?>"
                            data-summary-chip="<?= esc($chip['label']) ?>"
                            href="<?= $chip['url'] ?>"
                        >
                            <span class="events-toolbar-chip__count"><?= $chip['count'] ?></span>
                            <span><?= esc($chip['label']) ?></span>
                        </a>
                    <?php endforeach ?>
                </div>

                <form class="searchbar" method="get" action="<?= $page->url() ?>">
                    <?php if ($activeCategory !== 'all') : ?>
                        <input type="hidden" name="category" value="<?= esc($activeCategory) ?>">
                    <?php endif ?>
                    <?php if ($selectedDay !== '') : ?>
                        <input type="hidden" name="day" value="<?= esc($selectedDay) ?>">
                    <?php endif ?>
                    <input type="search" name="keyword" value="<?= esc($keyword) ?>" placeholder="Termine durchsuchen...">
                    <button type="submit" class="gs-c-btn" data-type="secondary" data-size="small">Suchen</button>
                </form>

                <div class="events-filter-row">
                    <?php foreach ($filters as $filter) : ?>
                        <a
                            class="events-filter-pill gs-c-btn"
                            data-active="<?= $filter['is_active'] ? 'true' : 'false' ?>"
                            data-category-slug="<?= esc($filter['slug']) ?>"
                            data-type="<?= $filter['is_active'] ? 'primary' : 'secondary' ?>"
                            data-size="small"
                            data-style="pill"
                            href="<?= $filter['url'] ?>"
                        >
                            <span><?= esc($filter['label']) ?></span>
                            <span class="events-filter-pill__count"><?= $filter['count'] ?></span>
                        </a>
                    <?php endforeach ?>
                </div>
            </div>

            <div class="events-calendar-shell mb-4">
                <div class="events-calendar-shell__header">
                    <div></div>
                    <div class="events-calendar-actions">
                        <a class="events-calendar-picker__toggle" href="<?= $buildUrl(['calendar' => 'open']) ?>" data-calendar-open>Kalender öffnen</a>
                    </div>
                </div>

                <div class="events-calendar-strip">
                    <?php foreach ($calendarDays as $day) : ?>
                        <a
                            class="events-day-chip gs-c-btn"
                            data-active="<?= $day['is_active'] ? 'true' : 'false' ?>"
                            data-type="<?= $day['is_active'] ? 'primary' : 'secondary' ?>"
                            data-size="regular"
                            data-style="rounded-corners"
                            data-day-key="<?= esc($day['key']) ?>"
                            href="<?= $day['url'] ?>"
                        >
                            <span class="events-day-chip__weekday"><?= esc($day['is_today'] ? 'Heute' : $day['weekday']) ?></span>
                            <strong class="events-day-chip__day"><?= esc($day['day']) ?></strong>
                            <span class="events-day-chip__month"><?= esc($day['month']) ?></span>
                        </a>
                    <?php endforeach ?>
                </div>
            </div>
        </div>

            <div class="events-calendar-modal" data-open="<?= $calendarOverlay['is_open'] ? 'true' : 'false' ?>">
                <div class="events-calendar-modal__backdrop"></div>
                <div class="events-calendar-modal__panel">
                    <div class="events-calendar-modal__header">
                        <a class="events-calendar-modal__nav" href="<?= $calendarOverlay['prev_url'] ?>" aria-label="Vorheriger Monat" data-calendar-nav="prev">‹</a>
                        <h3 class="events-calendar-modal__title" data-calendar-title><?= esc($calendarOverlay['title']) ?></h3>
                        <a class="events-calendar-modal__nav" href="<?= $calendarOverlay['next_url'] ?>" aria-label="Nächster Monat" data-calendar-nav="next">›</a>
                    </div>

                    <div class="events-calendar-modal__weekdays">
                        <?php foreach ($calendarOverlay['weekdays'] as $weekday) : ?>
                            <span><?= esc($weekday) ?></span>
                        <?php endforeach ?>
                    </div>

                    <div class="events-calendar-modal__days" data-calendar-days>
                        <?php foreach ($calendarOverlay['days'] as $day) : ?>
                            <a
                                class="events-calendar-modal__day"
                                data-current-month="<?= $day['is_current_month'] ? 'true' : 'false' ?>"
                                data-selected="<?= $day['is_selected'] ? 'true' : 'false' ?>"
                                data-today="<?= $day['is_today'] ? 'true' : 'false' ?>"
                                data-day-key="<?= esc($day['key']) ?>"
                                href="<?= $day['url'] ?>"
                            >
                                <span><?= esc($day['day']) ?></span>
                                <?php if ($day['has_events']) : ?>
                                    <i></i>
                                <?php endif ?>
                            </a>
                        <?php endforeach ?>
                    </div>

                    <a class="events-calendar-modal__close" href="<?= $calendarOverlay['close_url'] ?>" data-calendar-close>Schließen</a>
                </div>
            </div>

            <script type="application/json" id="events-page-data"><?= json_encode([
                'events' => $clientEvents,
                'today' => $todayKey,
                'selectedDay' => $selectedDay,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

        <section class="grid-item" data-span="1/1">
            <div class="events-results-head mb-3">
                <div>
                    <h1 class="font-title mb-2" data-results-title>
                        <?php if ($selectedDayLabel) : ?>
                            <?= esc($selectedDayLabel) ?>
                        <?php elseif ($keyword !== '') : ?>
                            Suchergebnisse für "<?= esc($keyword) ?>"
                        <?php else : ?>
                            <?= $page->headline() ?>
                        <?php endif ?>
                    </h1>
                    <p class="font-body"><?= $page->description() ?></p>
                </div>
                <p class="font-subheadline events-results-head__count" data-results-count><?= $totalResults ?> Ergebnisse</p>
            </div>

            <?php if ($events !== []) : ?>
                <ul class="grid events-grid mb-4" data-events-results>
                    <?php foreach ($events as $event) : ?>
                        <?php snippet('content-types/events/eventsListItem', ['event' => $event, 'variant' => 'event-list']) ?>
                    <?php endforeach ?>
                </ul>
            <?php else : ?>
                <div class="events-empty-state" data-events-empty>
                    <h3 class="font-headline mb-2">Keine passenden Termine gefunden</h3>
                    <p class="font-body mb-3">Passe Suchbegriff, Datum oder Kategorie an, um wieder mehr Veranstaltungen zu sehen.</p>
                    <a class="gs-c-btn" data-type="secondary" data-size="small" href="<?= $buildUrl(['keyword' => null, 'category' => null, 'day' => null, 'page' => null]) ?>">Filter zurücksetzen</a>
                </div>
            <?php endif ?>

            <div class="pagination events-pagination" data-events-pagination<?= ($selectedDay !== '' || $totalResults <= 12) ? ' hidden' : '' ?>>
                <a
                    href="<?= $pagination['prev_url'] ?? $buildUrl(['page' => 1]) ?>"
                    class="gs-c-btn"
                    data-type="secondary"
                    data-size="regualr"
                    data-style="pill"
                    data-pagination-prev
                    <?= empty($pagination['prev_url']) ? 'hidden' : '' ?>
                >Vorherige Seite</a>
                <a
                    href="<?= $pagination['next_url'] ?? $buildUrl(['page' => 2]) ?>"
                    class="gs-c-btn"
                    data-type="secondary"
                    data-size="regualr"
                    data-style="pill"
                    data-pagination-next
                    <?= empty($pagination['next_url']) ? 'hidden' : '' ?>
                >Nächste Seite</a>
            </div>
        </section>
    </section>
</main>

<?= js('assets/js/events-page.js') ?>
<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
