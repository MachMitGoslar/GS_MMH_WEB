<?php
/**
 * Newsletter Template - Redesigned to match PDF layout
 */
?>
<?php snippet('general/head', slots: true); ?>

<?php slot('head') ?>
<link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0/mapbox-gl.js"></script>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<?php endslot() ?>

<?php endsnippet() ?>

<?php snippet('general/header'); ?>

<main class="main">
  <!-- Newsletter Hero Section -->
  <section class="newsletter-cover">
    <div class="newsletter-cover-content">
      <img class="newsletter-logo" src="/assets/svg/RZ-RGB_MM!2_iv.svg" alt="MachMit!Haus Logo">
      <h1 class="font-title3XXL newsletter-title">Newsletter</h1>
      <h2 class="font-titleXL newsletter-date"><?= $page->title() ?></h2>
    </div>
  </section>

  <!-- Author Section -->
  <?php if ($author = $page->author()->toPage()): ?>
    <section class="grid content mb-7">
      <div class="newsletter-author-section grid-item" data-span="1/1">
        <div class="newsletter-author-content">
          <div class="newsletter-author-profile">
            <?php if ($authorImage = $author->cover()): ?>
              <img src="<?= $authorImage->url() ?>" alt="<?= $author->title() ?>" class="author-avatar">
            <?php endif ?>
            <div class="author-info">
              <h3 class="font-headline author-name"><?= $author->title() ?></h3>
              <p class="font-footnote author-role">Autor</p>
            </div>
          </div>
          <div class="newsletter-author-message">
            <?= $page->greeting_text() ? $page->greeting_text()->kt() : 'Willkommen zu unserem Newsletter! Hier erfahren Sie alles über die aktuellen Projekte und Veranstaltungen im MachMit!Haus.' ?>
          </div>
        </div>
      </div>
    </section>
  <?php endif ?>    
  <!-- Weekly Schedule Section -->
    <?php if ($page->weekly_dates() && $page->weekly_dates()->isNotEmpty()): ?>
      <section class="grid content mb-7">
        <div class="grid-item" data-span="1/1">
          <h2 class="font-title mb-3">Die Woche im MachMit!Haus</h2>
        <div class="weekly-calendar">
          <div class="calendar-grid">
            <?php
            $weeklyEvents = $page->weekly_dates()->toStructure();
            $daysOfWeek = [
              'monday' => 0,
              'tuesday' => 1,
              'wednesday' => 2,
              'thursday' => 3,
              'friday' => 4,
            ];
            $dayNames = [
              'monday' => 'Mo',
              'tuesday' => 'Di',
              'wednesday' => 'Mi',
              'thursday' => 'Do',
              'friday' => 'Fr'
            ];

            // Initialize calendar grid for full week
            $calendar = array_fill(0, 5, []);

            // Group events by day
            foreach ($weeklyEvents as $event) {
              $dayKey = $event->day()->value();
              if (isset($daysOfWeek[$dayKey])) {
                $calendar[$daysOfWeek[$dayKey]][] = $event;
              }
            }

            // Display calendar grid for full week
            for ($day = 0; $day < 5; $day++):
              $dayKeys = array_keys($dayNames);
              $currentDayKey = $dayKeys[$day] ?? '';
              $currentDayName = $dayNames[$currentDayKey] ?? '';
              ?>
              <div class="calendar-day">
                <div class="font-headline calendar-day-label"><?= $currentDayName ?></div>
                <div class="calendar-events">
                  <?php if (!empty($calendar[$day])): ?>
                    <?php foreach ($calendar[$day] as $event): ?>
                      <div class="calendar-event mb-2 p-2 bg-gray-100 rounded">
                        <div class="font-footnote event-time"><?= $event->start_time()->toDate("H:i", $fallback = null) ?></div>
                        <div class="font-body event-title"><?= $event->activity() ?></div>
                        <?php if ($event->location() && $event->location()->isNotEmpty()): ?>
                          <div class="font-caption event-location"><?= $event->location() ?></div>
                        <?php endif ?>
                      </div>
                    <?php endforeach ?>
                  <?php else: ?>
                    <div class="font-body calendar-empty">-</div>
                  <?php endif ?>
                </div>
              </div>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    <?php endif ?>
      </section>


    <!-- Upcoming Dates Section -->
    <?php if ($page->upcomming_dates() && $page->upcomming_dates()->isNotEmpty()): ?>
      <section class="grid content mb-7">
        <div class="grid-item" data-span="1/1">
          <h2 class="font-title mb-4">Kommende Termine</h2>
          
          <?php 
          // Group events by month
          $upcomingEvents = $page->upcomming_dates()->toStructure();
          $eventsByMonth = [];
          
          foreach ($upcomingEvents as $event) {
            if ($event->show_event_date()->toBool() && $event->event_date()->isNotEmpty()) {
              $monthKey = $event->event_date()->toDate('Y-m');
              $monthName = $event->event_date()->toDate('F Y');
              
              if (!isset($eventsByMonth[$monthKey])) {
                $eventsByMonth[$monthKey] = [
                  'name' => $monthName,
                  'events' => []
                ];
              }
              $eventsByMonth[$monthKey]['events'][] = $event;
            }
          }
          
          // Sort months chronologically
          ksort($eventsByMonth);
          ?>
          
          <div class="newsletter-months-grid">
          <?php foreach ($eventsByMonth as $monthData): ?>
            <div class="newsletter-month-item">
              <h3 class="font-headline mb-3"><?= $monthData['name'] ?></h3>
              <ul class="events-list">
                <?php foreach ($monthData['events'] as $event): ?>
                  <li class="eventsListItem">
                    <time class="font-footnote mb-2">
                      <?= $event->event_date()->toDate('d.m.Y') ?>
                      <?php if ($event->event_date_end()->isNotEmpty()): ?>
                        - <?= $event->event_date_end()->toDate('d.m.Y') ?>
                      <?php endif ?>
                    </time>
                    <h4 class="font-subheadline mb-1"><?= $event->event_name() ?></h4>
                    <?php if ($event->event_description()->isNotEmpty()): ?>
                      <p class="font-body mb-1"><?= $event->event_description() ?></p>
                    <?php endif ?>
                    <?php if ($event->event_location()->isNotEmpty()): ?>
                      <p class="font-footnote text-gray-600">📍 <?= $event->event_location() ?></p>
                    <?php endif ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
          </div>
          
        </div>
      </section>
    <?php endif ?>

    <!-- Timeline/Year in Review Section -->
    <?php if ($page->timeline() && $page->timeline()->isNotEmpty()): ?>
      <section class="grid content mb-7">
        <div class="grid-item" data-span="1/1">
          <h2 class="font-title mb-3">Jahresrückblick</h2>
          <div class="timeline-container">
            <?php
            $timelineEntries = $page->timeline()->toStructure();
            $isLeft = true;
            foreach ($timelineEntries as $entry):
              ?>
              <div class="timeline-item <?= $isLeft ? 'timeline-item--left' : 'timeline-item--right' ?>">
                <?php if ($isLeft): ?>
                  <div class="timeline-item__container">
                    <!-- Left side: Text | Image | Connector -->
                    <div class="timeline-content">
                      <div class="font-headline timeline-date"><?= $entry->year() ?></div>
                      <div class="font-body timeline-text"><?= $entry->summary() ?></div>
                    </div>
                    <div class="timeline-image">
                      <?php if ($entry->image()->isNotEmpty() && $imageFile = $entry->image()->toFile()): ?>
                        <img src="<?= $imageFile->url() ?>" alt="<?= $entry->year() ?>" loading="lazy">
                      <?php endif ?>
                    </div>
                    <div class="timeline-connector"></div>
                  </div>
                <?php else: ?>
                  <!-- Right side: Connector | Image | Text -->
                  <div class="timeline-item__container">
                    <div class="timeline-connector"></div>
                    <div class="timeline-image">
                      <?php if ($entry->image()->isNotEmpty() && $imageFile = $entry->image()->toFile()): ?>
                        <img src="<?= $imageFile->url() ?>" alt="<?= $entry->year() ?>" loading="lazy">
                      <?php endif ?>
                    </div>
                    <div class="timeline-content">
                      <div class="font-headline timeline-date"><?= $entry->year() ?></div>
                      <div class="font-body timeline-text"><?= $entry->summary() ?></div>
                    </div>
                  </div>
                <?php endif ?>
              </div>
              <?php
              $isLeft = !$isLeft;
            endforeach;
            ?>
          </div>
        </div>
      </section>
    <?php endif ?>


    <!-- Reviews Section -->
    <?php if ($page->review_entries() && $page->review_entries()->isNotEmpty()): ?>
      <section class="grid content mb-7">
        <div class="grid-item" data-span="1/1">
          <h2 class="font-title mb-4">Rückblicke</h2>
          <ul class="grid newsletter-grid mb-4">
            <?php foreach ($page->review_entries()->toStructure() as $review): ?>
              <li class="c-projectUpdateTeaser-card">
                <?php if ($review->image()->isNotEmpty() && $imageFile = $review->image()->toFile()): ?>
                  <div>
                    <img class="hero" src="<?= $imageFile->url() ?>" alt="<?= $review->headline() ?>">
                  </div>
                <?php endif ?>
                <div class="content">
                  <div class="statusheader mb-2">
                    <div class="status-badge">📖 Rückblick</div>
                  </div>
                  <h3 class="font-headline font-line-height-narrow mb-2"><?= $review->headline() ?></h3>
                  <?php if ($review->subheadline()->isNotEmpty()): ?>
                    <h4 class="font-subheadline font-line-height-narrow mb-2"><?= $review->subheadline() ?></h4>
                  <?php endif ?>
                  <p class="font-body"><?= $review->content_text() ? $review->content_text()->excerpt(100) : '' ?></p>
                  <?php if ($review->date()->isNotEmpty()): ?>
                    <p class="font-footnote mt-2"><?= $review->date()->toDate('d.m.Y') ?></p>
                  <?php endif ?>
                </div>
              </li>
            <?php endforeach ?>
          </ul>
        </div>
      </section>
    <?php endif ?>
    <!-- Current Projects Section -->
    <?php if ($page->actual_entries() && $page->actual_entries()->isNotEmpty()): ?>
      <section class="grid content mb-7">
        <div class="grid-item" data-span="1/1">
          <h2 class="font-title mb-4">Aktuelle Projekte</h2>
          <ul class="grid newsletter-grid mb-4">
            <?php foreach ($page->actual_entries()->toStructure() as $project): ?>
              <li class="c-projectUpdateTeaser-card">
                <?php if ($project->image()->isNotEmpty() && $imageFile = $project->image()->toFile()): ?>
                  <div>
                    <img class="hero" src="<?= $imageFile->url() ?>" alt="<?= $project->headline() ?>">
                  </div>
                <?php endif ?>
                <div class="content">
                  <div class="statusheader mb-2">
                    <div class="status-badge" data-color="active">🚀 Aktuell</div>
                  </div>
                  <h3 class="font-headline font-line-height-narrow mb-2"><?= $project->headline() ?></h3>
                  <?php if ($project->subheadline()->isNotEmpty()): ?>
                    <h4 class="font-subheadline font-line-height-narrow mb-2"><?= $project->subheadline() ?></h4>
                  <?php endif ?>
                  <p class="font-body"><?= $project->content_text() ? $project->content_text()->excerpt(100) : '' ?></p>
                  <?php if ($project->location()->isNotEmpty()): ?>
                    <p class="font-footnote mt-2">📍 <?= $project->location() ?></p>
                  <?php endif ?>
                </div>
              </li>
            <?php endforeach ?>
          </ul>
        </div>
      </section>
    <?php endif ?>





    <!-- Previews Section -->
    <?php if ($page->upcomming_entries() && $page->upcomming_entries()->isNotEmpty()): ?>
      <section class="grid content mb-7">
        <div class="grid-item" data-span="1/1">
          <h2 class="font-title mb-4">Vorschau</h2>
          <div class="grid newsletter-grid mb-4">
            <?php foreach ($page->upcomming_entries()->toStructure() as $preview): ?>
              <div class="grid-item c-projectUpdateTeaser-card" data-span="1/1@s 1/2@m 1/3@l">
                <?php if ($preview->image()->isNotEmpty() && $imageFile = $preview->image()->toFile()): ?>
                  <div>
                    <img class="hero" src="<?= $imageFile->url() ?>" alt="<?= $preview->headline() ?>">
                  </div>
                <?php endif ?>
                <div class="content">
                  <div class="statusheader mb-2">
                    <div class="status-badge" data-color="planning">🔮 Vorschau</div>
                  </div>
                  <h3 class="font-headline font-line-height-narrow mb-2"><?= $preview->headline() ?></h3>
                  <?php if ($preview->subheadline()->isNotEmpty()): ?>
                    <h4 class="font-subheadline font-line-height-narrow mb-2"><?= $preview->subheadline() ?></h4>
                  <?php endif ?>
                  <p class="font-body"><?= $preview->content_text() ? $preview->content_text()->excerpt(100) : '' ?></p>
                  <?php if ($preview->date()->isNotEmpty()): ?>
                    <p class="font-footnote mt-2">📅 <?= $preview->date()->toDate('d.m.Y') ?></p>
                  <?php endif ?>
                </div>
              </div>
            <?php endforeach ?>
          </div>
        </div>
      </section>
    <?php endif ?>

    <!-- News Section -->
    <?php if ($page->news() && $page->news()->isNotEmpty()): ?>
      <section class="grid content mb-7">
        <div class="grid-item" data-span="1/1">
          <h2 class="font-title mb-4">Nachrichten aus dem MachMit!Haus</h2>
          <div class="grid newsletter-grid mb-4">
            <?php foreach ($page->news()->toStructure() as $newsItem): ?>
              <div class="grid-item c-projectUpdateTeaser-card" data-span="1/1@s 1/2@m 1/3@l">
                <?php if ($newsItem->image()->isNotEmpty() && $imageFile = $newsItem->image()->toFile()): ?>
                  <div>
                    <img class="hero" src="<?= $imageFile->url() ?>" alt="<?= $newsItem->headline() ?>">
                  </div>
                <?php endif ?>
                <div class="content">
                  <div class="statusheader mb-2">
                    <div class="status-badge">📰 Nachrichten</div>
                  </div>
                  <h3 class="font-headline font-line-height-narrow mb-2"><?= $newsItem->headline() ?></h3>
                  <?php if ($newsItem->subheadline()->isNotEmpty()): ?>
                    <h4 class="font-subheadline font-line-height-narrow mb-2"><?= $newsItem->subheadline() ?></h4>
                  <?php endif ?>
                  <p class="font-body"><?= $newsItem->content_text() ? $newsItem->content_text()->excerpt(100) : '' ?></p>
                  <?php if ($newsItem->link()->isNotEmpty()): ?>
                    <a href="<?= $newsItem->link() ?>" class="gs-c-btn mt-3" data-type="secondary" data-size="small" target="_blank">🔗 Weiterlesen</a>
                  <?php endif ?>
                </div>
              </div>
            <?php endforeach ?>
          </div>
        </div>
      </section>
    <?php endif ?>


    <!-- Contact & Info Footer -->
    <section class="grid content mb-7">
      <?php if ($page->closingsinfos() && $page->closingsinfos()->isNotEmpty()): ?>
        <div class="c-newsletter-teaser grid-item" data-span="1/1">
          <div class="flex items-center mb-5">
            <?php if (isset($authorImage) && $authorImage): ?>
              <img src="<?= $authorImage->url() ?>" alt="Kontakt" class="author-avatar">
            <?php endif ?>
            <div>
              <h3 class="font-headline color-fg-light mb-1">Mehr Informationen?</h3>
              <p class="font-footnote color-fg-light">Haben Sie Fragen oder Anregungen?</p>
            </div>
          </div>
          <div class="font-body color-fg-light mb-4">
            <?= $page->closingsinfos()->kt() ?>
          </div>
        </div>
      <?php endif ?>
      
      <div class="grid-item" data-span="1/2">
        <h3 class="font-title mb-3">MachMit!Haus Kontakt</h3>
        <div class="contact-info mb-4">
          <div class="contact-item font-body mb-2">
            <span>📧</span> <a href="mailto:machmit@goslar.de">machmit@goslar.de</a>
          </div>
          <div class="contact-item font-body mb-2">
            <span>🌐</span> <a href="https://machmit.goslar.de">machmit.goslar.de</a>
          </div>
          <div class="contact-item font-body mb-2">
            <span>📞</span> <a href="tel:05321704525">05321 704 525</a>
          </div>
        </div>
        <div class="social-info">
          <p class="font-footnote">Folgen Sie uns: <strong>@machmitgoslar</strong></p>
        </div>
      </div>
      
      <div class="grid-item" data-span="1/2">
        <h3 class="font-title mb-3">Standort</h3>
        <div id="map" class="mb-4"></div>
        <p class="font-footnote">Markt 7, 38640 Goslar</p>
      </div>
    </section>
        <script>
            mapboxgl.accessToken = 'pk.eyJ1IjoicmFuZ2FyaWFuIiwiYSI6ImNrZGVxNzNhODI5MTcyenM4dGR5bnZhb3UifQ.7WvcNEBQJn9iV42IiyG8rQ';
            const map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/mapbox/standard', // Use the standard style for the map
                projection: 'globe', // display the map as a globe
                attributionControl: false,
                zoomControl: false,
                zoom: 15, // initial zoom level, 0 is the world view, higher values zoom in
                center: [10.429327, 51.906169] // center the map on this longitude and latitude
            });

            map.scrollZoom.disable();

            const popup = new mapboxgl.Popup({
              anchor: "right"
            })
  .setHTML('<h3>MachMit!Haus</h3><p>Markt 7 | 38640 Goslar</p>');

            const el = document.createElement('div');
            el.className = 'custom-marker';
            el.innerHTML = `
              <img src="/assets/svg/map_pin.svg" alt="Marker" style="width:32px;height:32px;">
            `;

            const marker = new mapboxgl.Marker({
              element: el
              
            })
              .setLngLat([10.429327, 51.906169])
              .addTo(map);

            marker.setPopup(popup);  
            map.on('style.load', () => {
                map.setFog({}); // Set the default atmosphere style
            });
        </script>
</main>

<?php snippet('general/footer'); ?>