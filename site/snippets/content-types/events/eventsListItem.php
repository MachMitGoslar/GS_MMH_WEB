<?php

use Kirby\Toolkit\Str;

/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
* @var array $event
*/

$variant = $variant ?? 'default';

$eventData = $event['raw'] ?? $event;
$source = $eventData['event'] ?? [];
$startValue = $event['start'] ?? ($eventData['start'] ?? null);
$start = $startValue instanceof DateTimeInterface
    ? $startValue
    : new DateTimeImmutable((string) ($startValue ?? 'now'));
$title = trim((string) ($event['title'] ?? ($source['name'] ?? 'Termin')));
$description = trim(strip_tags((string) ($event['description'] ?? ($source['description'] ?? ''))));
$url = (string) ($event['url'] ?? ('https://oveda.de/eventdate/' . ($eventData['id'] ?? '')));
$categories = $event['categories'] ?? [];

if ($categories === []) {
    foreach ($source['categories'] ?? [] as $category) {
        $name = trim((string) ($category['name'] ?? ''));
        if ($name !== '') {
            $categories[] = $name;
        }
    }

    foreach ($source['custom_categories'] ?? [] as $category) {
        $name = trim((string) ($category['name'] ?? ''));
        if ($name !== '') {
            $categories[] = $name;
        }
    }
}

$place = $source['place'] ?? [];
$placeLocation = $place['location'] ?? [];
$location = trim((string) ($event['location'] ?? ''));
if ($location === '') {
    $locationBits = [
        trim((string) ($place['name'] ?? '')),
        trim((string) ($placeLocation['street'] ?? '')),
        trim((string) ($placeLocation['city'] ?? '')),
    ];
    $location = implode(', ', array_values(array_filter($locationBits)));
}

$photo = $event['photo'] ?? ($source['photo']['image_url'] ?? null);
if (is_string($photo) && $photo !== '' && str_starts_with($photo, '/')) {
    $photo = 'https://oveda.de' . $photo;
}

$isFree = (bool) ($event['is_free'] ?? ($source['accessible_for_free'] ?? false));
$timeLabel = trim((string) ($event['time_label'] ?? ''));
if ($timeLabel === '') {
    $end = !empty($eventData['end']) ? new DateTimeImmutable((string) $eventData['end']) : null;
    $timeLabel = ($eventData['allday'] ?? false)
        ? 'Ganztägig'
        : $start->format('H:i') . ($end ? ' - ' . $end->format('H:i') : '');
}
?>
<li class="eventsListItem">
  <?php if ($variant === 'card') : ?>
    <a class="eventsListItem__link" href="<?= esc($url) ?>" target="_blank" rel="noopener noreferrer">
      <div
        class="eventsListItem__media"
        <?= $photo ? 'style="background-image:url(\'' . esc($photo) . '\')"' : '' ?>
      >
        <div class="eventsListItem__overlay"></div>
        <div class="eventsListItem__badges">
          <?php if ($categories !== []) : ?>
            <span class="eventsListItem__badge"><?= esc($categories[0]) ?></span>
          <?php endif ?>
          <?php if ($isFree) : ?>
            <span class="eventsListItem__badge eventsListItem__badge--free">Kostenlos</span>
          <?php endif ?>
        </div>

        <div class="eventsListItem__meta">
          <div>
            <p class="eventsListItem__meta-line"><?= esc($timeLabel) ?><?php if ($location !== '') : ?> · <?= esc(Str::short($location, 34, '…')) ?><?php endif ?></p>
            <h3 class="font-subheadline"><?= esc(Str::short($title, 68, '…')) ?></h3>
            <?php if ($description !== '') : ?>
              <p class="eventsListItem__description"><?= esc(Str::short($description, 110, '…')) ?></p>
            <?php endif ?>
          </div>
          <div class="eventsListItem__dateBadge">
            <span><?= esc($event['date_badge_month'] ?? $start->format('M')) ?></span>
            <strong><?= esc($start->format('d')) ?></strong>
          </div>
        </div>
      </div>
    </a>
  <?php else : ?>
    <?php
      $date = strtotime((string) ($eventData['start'] ?? $start->format(DATE_ATOM)));
      $displayTitle = $variant === 'event-list' ? $title : Str::short($title, 50, '…');
      ?>
    <a href="<?= esc($url) ?>" target="_blank" rel="noopener noreferrer">
      <time class="font-footnote mb-2"><?= date('d.m.Y, H:i', $date) ?> Uhr</time>
      <h3 class="font-subheadline mb-2"><?= esc($displayTitle) ?></h3>
      <p class="font-body mb-2"><?= Str::short($description, 120, '…') ?></p>
    </a>
  <?php endif ?>
</li>
