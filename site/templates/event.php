<?php

/**
 * Detail view for a single Oveda event date.
 *
 * @var Kirby\Cms\Site $site
 * @var Kirby\Cms\Page $page
 * @var array $detail
 */

use Kirby\Toolkit\Str;

$photo = $detail['photo'];
$place = $detail['place'];
$organizer = $detail['organizer'];
$organization = $detail['organization'];

$mapboxToken = trim((string) $site->find('contact')?->mapbox_token()->value());
$hasMap = $mapboxToken !== ''
    && $place['latitude'] !== null
    && $place['longitude'] !== null;

$notices = [];

if ($detail['is_cancelled'] === true) {
    $notices[] = ['icon' => 'alert', 'tone' => 'critical', 'text' => 'Diese Veranstaltung wurde abgesagt.'];
} elseif ($detail['is_postponed'] === true) {
    $notices[] = ['icon' => 'alert', 'tone' => 'critical', 'text' => 'Dieser Termin wurde verschoben.'];
} elseif ($detail['is_past'] === true) {
    $notices[] = ['icon' => 'info', 'tone' => 'muted', 'text' => 'Dieser Termin liegt in der Vergangenheit.'];
}

if ($detail['booked_up'] === true) {
    $notices[] = ['icon' => 'alert', 'tone' => 'warning', 'text' => 'Die Veranstaltung ist ausgebucht.'];
}

$contacts = [];

if ($organizer['email'] !== null) {
    $contacts[] = ['icon' => 'mail', 'label' => $organizer['email'], 'href' => 'mailto:' . $organizer['email']];
} elseif ($organization['email'] !== null) {
    $contacts[] = ['icon' => 'mail', 'label' => $organization['email'], 'href' => 'mailto:' . $organization['email']];
}

if ($organizer['phone'] !== null) {
    $contacts[] = ['icon' => 'phone', 'label' => $organizer['phone'], 'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $organizer['phone'])];
} elseif ($organization['phone'] !== null) {
    $contacts[] = ['icon' => 'phone', 'label' => $organization['phone'], 'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $organization['phone'])];
}

if ($organizer['url'] !== null) {
    $contacts[] = ['icon' => 'globe', 'label' => 'Website des Veranstalters', 'href' => $organizer['url']];
}

// schema.org Event, so search engines can show the date, place and price
$eventSchema = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Event',
    'name' => $detail['title'],
    'url' => $page->url(),
    'startDate' => $detail['start']->format('c'),
    'endDate' => $detail['end']?->format('c'),
    'eventStatus' => match (true) {
        $detail['is_cancelled'] => 'https://schema.org/EventCancelled',
        $detail['is_postponed'] => 'https://schema.org/EventPostponed',
        default => 'https://schema.org/EventScheduled',
    },
    'eventAttendanceMode' => match ($detail['attendance_mode']) {
        'online' => 'https://schema.org/OnlineEventAttendanceMode',
        'mixed' => 'https://schema.org/MixedEventAttendanceMode',
        default => 'https://schema.org/OfflineEventAttendanceMode',
    },
    'description' => Str::excerpt(strip_tags($detail['description']), 500, false) ?: null,
    'image' => $photo['url'],
    'inLanguage' => 'de-DE',
]);

if ($detail['address_lines'] !== []) {
    $eventSchema['location'] = array_filter([
        '@type' => 'Place',
        'name' => $place['name'] ?: implode(', ', $detail['address_lines']),
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $place['street'] ?: null,
            'postalCode' => $place['zip'] ?: null,
            'addressLocality' => $place['city'] ?: null,
            'addressCountry' => 'DE',
        ]),
        'geo' => $place['latitude'] !== null && $place['longitude'] !== null ? [
            '@type' => 'GeoCoordinates',
            'latitude' => $place['latitude'],
            'longitude' => $place['longitude'],
        ] : null,
    ]);
}

if ($organizer['name'] !== '') {
    $eventSchema['organizer'] = array_filter([
        '@type' => 'Organization',
        'name' => $organizer['name'],
        'url' => $organizer['url'],
        'email' => $organizer['email'],
        'telephone' => $organizer['phone'],
    ]);
}

if ($detail['is_free'] === true || $detail['price_info'] !== '') {
    $eventSchema['offers'] = array_filter([
        '@type' => 'Offer',
        'url' => $detail['ticket_link'] ?? $page->url(),
        'availability' => $detail['booked_up']
            ? 'https://schema.org/SoldOut'
            : 'https://schema.org/InStock',
        'price' => $detail['is_free'] ? '0' : null,
        'priceCurrency' => $detail['is_free'] ? 'EUR' : null,
        'description' => $detail['is_free'] ? 'Kostenlos' : $detail['price_info'],
    ]);
}
?>

<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main class="main main--event">
  <article class="c-event content" data-cancelled="<?= $detail['is_cancelled'] ? 'true' : 'false' ?>">

    <a class="c-event__back" href="<?= url('events') ?>">
      <?php snippet('content-types/events/eventIcon', ['icon' => 'arrow-left', 'size' => 18]) ?>
      <span>Alle Veranstaltungen</span>
    </a>

    <div class="c-event__header">
      <div class="c-event__media">
        <?php if ($photo['url']) : ?>
          <img class="c-event__image" src="<?= esc($photo['url']) ?>" alt="<?= esc($detail['title']) ?>" loading="lazy">
        <?php else : ?>
          <?php snippet('utilities/imagePlaceholder') ?>
        <?php endif ?>

        <div class="c-event__dateBadge" aria-hidden="true">
          <span><?= esc($detail['start']->format('M')) ?></span>
          <strong><?= esc($detail['start']->format('d')) ?></strong>
        </div>
      </div>

      <div class="c-event__intro">
        <div class="c-event__pills">
          <?php if ($detail['countdown_label'] !== null && $detail['is_past'] === false) : ?>
            <span class="c-event__pill c-event__pill--accent"><?= esc($detail['countdown_label']) ?></span>
          <?php endif ?>
          <?php if ($detail['is_free'] === true) : ?>
            <span class="c-event__pill c-event__pill--free">Kostenlos</span>
          <?php endif ?>
          <?php foreach ($detail['categories'] as $category) : ?>
            <span class="c-event__pill"><?= esc($category) ?></span>
          <?php endforeach ?>
        </div>

        <h1 class="font-title c-event__title"><?= esc($detail['title']) ?></h1>

        <p class="c-event__when">
          <?php snippet('content-types/events/eventIcon', ['icon' => 'calendar', 'size' => 18]) ?>
          <span><?= esc($detail['date_label']) ?> · <?= esc($detail['time_label']) ?></span>
        </p>
      </div>
    </div>

    <?php if ($notices !== []) : ?>
      <div class="c-event__notices">
        <?php foreach ($notices as $notice) : ?>
          <p class="c-event__notice" data-tone="<?= esc($notice['tone']) ?>">
            <?php snippet('content-types/events/eventIcon', ['icon' => $notice['icon'], 'size' => 18]) ?>
            <span><?= esc($notice['text']) ?></span>
          </p>
        <?php endforeach ?>
      </div>
    <?php endif ?>

    <section class="c-event__glance" data-has-map="<?= $hasMap ? 'true' : 'false' ?>" aria-label="Auf einen Blick">
      <div class="c-event__facts">
          <?php foreach ($detail['facts'] as $fact) : ?>
            <div class="c-event__fact">
              <span class="c-event__factIcon">
                <?php snippet('content-types/events/eventIcon', ['icon' => $fact['icon']]) ?>
              </span>
              <span class="c-event__factBody">
                <span class="c-event__factLabel"><?= esc($fact['label']) ?></span>
                <span class="c-event__factValue"><?= esc($fact['value']) ?></span>
                <?php if (isset($fact['href'])) : ?>
                  <a class="c-event__factLink" href="<?= esc($fact['href']) ?>" target="_blank" rel="noopener noreferrer">
                    <?= esc($fact['href_label'] ?? 'Öffnen') ?>
                    <?php snippet('content-types/events/eventIcon', ['icon' => 'external-link', 'size' => 14]) ?>
                  </a>
                <?php endif ?>
              </span>
            </div>
        <?php endforeach ?>
      </div>

      <?php if ($hasMap) : ?>
        <div class="c-event__map">
          <div
            class="c-event__mapCanvas"
            id="event-map"
            data-event-map
            data-lat="<?= esc($place['latitude']) ?>"
            data-lng="<?= esc($place['longitude']) ?>"
            data-token="<?= esc($mapboxToken) ?>"
            data-label="<?= esc($place['name'] ?: implode(', ', $detail['address_lines'])) ?>"
            role="img"
            aria-label="Karte: <?= esc(implode(', ', $detail['address_lines'])) ?>"
          ></div>
          <?php if ($detail['maps_url'] !== null) : ?>
            <a class="c-event__mapLink" href="<?= esc($detail['maps_url']) ?>" target="_blank" rel="noopener noreferrer">
              <?php snippet('content-types/events/eventIcon', ['icon' => 'map-pin', 'size' => 14]) ?>
              <span>Route planen</span>
            </a>
          <?php endif ?>
        </div>
      <?php endif ?>
    </section>

    <div class="c-event__body">
      <div class="c-event__main">
        <?php if ($detail['description'] !== '') : ?>
          <section class="c-event__section">
            <h2 class="font-headline mb-2">Über die Veranstaltung</h2>
            <div class="c-event__description font-body"><?= nl2br(esc($detail['description'])) ?></div>
          </section>
        <?php endif ?>

        <?php if ($detail['tags'] !== []) : ?>
          <section class="c-event__section">
            <h2 class="font-headline mb-2">Schlagworte</h2>
            <ul class="c-event__tags">
              <?php foreach ($detail['tags'] as $tag) : ?>
                <li class="c-event__tag">
                  <?php snippet('content-types/events/eventIcon', ['icon' => 'tag', 'size' => 14]) ?>
                  <span><?= esc($tag) ?></span>
                </li>
              <?php endforeach ?>
            </ul>
          </section>
        <?php endif ?>

        <?php if ($detail['other_dates'] !== []) : ?>
          <section class="c-event__section">
            <h2 class="font-headline mb-2">
              <?php snippet('content-types/events/eventIcon', ['icon' => 'repeat', 'size' => 18]) ?>
              Weitere Termine
            </h2>
            <ul class="c-event__dates">
              <?php foreach ($detail['other_dates'] as $other) : ?>
                <li>
                  <a class="c-event__date" href="<?= esc($other['url']) ?>">
                    <span class="c-event__dateLabel"><?= esc($other['date_label']) ?></span>
                    <span class="c-event__dateTime"><?= esc($other['time_label']) ?></span>
                  </a>
                </li>
              <?php endforeach ?>
            </ul>
          </section>
        <?php endif ?>
      </div>

      <aside class="c-event__aside">
        <div class="c-event__card">
          <h2 class="font-subheadline mb-2">Jetzt merken</h2>
          <div class="c-event__actions">
            <?php if ($detail['ticket_link'] !== null) : ?>
              <a class="gs-c-btn" data-type="primary" data-size="regular" data-style="pill" href="<?= esc($detail['ticket_link']) ?>" target="_blank" rel="noopener noreferrer">
                <?php snippet('content-types/events/eventIcon', ['icon' => 'ticket', 'size' => 18]) ?>
                <span>Tickets</span>
              </a>
            <?php endif ?>

            <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill" href="<?= esc($detail['ics_url']) ?>">
              <?php snippet('content-types/events/eventIcon', ['icon' => 'download', 'size' => 18]) ?>
              <span>In den Kalender</span>
            </a>

            <?php if ($detail['external_link'] !== null) : ?>
              <a class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill" href="<?= esc($detail['external_link']) ?>" target="_blank" rel="noopener noreferrer">
                <?php snippet('content-types/events/eventIcon', ['icon' => 'external-link', 'size' => 18]) ?>
                <span>Mehr Infos</span>
              </a>
            <?php endif ?>
          </div>
        </div>

        <?php if ($detail['address_lines'] !== []) : ?>
          <div class="c-event__card">
            <h2 class="font-subheadline mb-2">
              <?php snippet('content-types/events/eventIcon', ['icon' => 'map-pin', 'size' => 18]) ?>
              Veranstaltungsort
            </h2>
            <address class="c-event__address">
              <?php foreach ($detail['address_lines'] as $line) : ?>
                <span><?= esc($line) ?></span>
              <?php endforeach ?>
            </address>
            <?php if ($detail['maps_url'] !== null) : ?>
              <a class="c-event__cardLink" href="<?= esc($detail['maps_url']) ?>" target="_blank" rel="noopener noreferrer">
                <span>Route planen</span>
                <?php snippet('content-types/events/eventIcon', ['icon' => 'external-link', 'size' => 14]) ?>
              </a>
            <?php endif ?>
          </div>
        <?php endif ?>

        <?php if ($organizer['name'] !== '' || $contacts !== []) : ?>
          <div class="c-event__card">
            <h2 class="font-subheadline mb-2">
              <?php snippet('content-types/events/eventIcon', ['icon' => 'user', 'size' => 18]) ?>
              Veranstalter
            </h2>
            <?php if ($organizer['name'] !== '') : ?>
              <p class="c-event__organizer"><?= esc($organizer['name']) ?></p>
            <?php endif ?>
            <?php if ($organization['name'] !== '' && $organization['name'] !== $organizer['name']) : ?>
              <p class="c-event__organizerMeta"><?= esc($organization['name']) ?></p>
            <?php endif ?>
            <?php if ($contacts !== []) : ?>
              <ul class="c-event__contacts">
                <?php foreach ($contacts as $contact) : ?>
                  <li>
                    <a href="<?= esc($contact['href']) ?>"<?= str_starts_with($contact['href'], 'http') ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                      <?php snippet('content-types/events/eventIcon', ['icon' => $contact['icon'], 'size' => 16]) ?>
                      <span><?= esc($contact['label']) ?></span>
                    </a>
                  </li>
                <?php endforeach ?>
              </ul>
            <?php endif ?>
          </div>
        <?php endif ?>

        <p class="c-event__source">
          <?php snippet('content-types/events/eventIcon', ['icon' => 'info', 'size' => 14]) ?>
          <span>
            Daten aus dem Veranstaltungskalender
            <a href="<?= esc($detail['source_url']) ?>" target="_blank" rel="noopener noreferrer">Oveda</a>.
            <?php if ($photo['url'] && $photo['copyright'] !== '') : ?>
              Bild: <?= esc($photo['copyright']) ?><?= $photo['license_code'] !== '' ? ' (' . esc($photo['license_code']) . ')' : '' ?>.
            <?php endif ?>
          </span>
        </p>
      </aside>
    </div>
  </article>
</main>

<script type="application/ld+json"><?= json_encode($eventSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<?php if ($hasMap) : ?>
  <?= js('assets/js/event-map.js?version=' . filemtime(kirby()->root('index') . '/assets/js/event-map.js')) ?>
<?php endif ?>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
