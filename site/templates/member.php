<?php
/**
* Member Detail Page Template
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php snippet('layout/head', slots: true); ?>

<?php slot('head') ?>
<?php snippet('layout/mapbox') ?>
<?php endslot() ?>

<?php endsnippet() ?>
<?php snippet('layout/header'); ?>

<main>
  <?php
    $memberCover = $page->cover();
$memberTeam = $page->teams()->isNotEmpty() ? ($page->teams()->split()[0] ?? null) : null;
?>
  <section class="member-detail">
    <?php snippet('sections/profile-backdrop', ['person' => $page, 'team' => $memberTeam]) ?>
    <div class="grid content">
      
      <!-- Hero Section -->
      <div class="grid-item member-hero" data-span="1/1">
        <div class="member-hero-content">
          <?php snippet('sections/profile-header', ['shape' => 'round'], slots: true) ?>
            <?php slot('media') ?>
              <?php snippet('utilities/avatar', ['person' => $page, 'size' => 480, 'loading' => 'eager']) ?>
            <?php endslot() ?>

            <?php slot('intro') ?>
              <?php snippet('utilities/page-title', [
              'title' => $page->name()->html(),
              'subtitle' => $page->role()->isNotEmpty() ? $page->role()->html() : null,
              'after' => $page->teams()->isNotEmpty() ? function () use ($page) {
                  $teamLabels = [
                      'staff' => 'Hauptamtliches Team',
                      'volunteer' => 'Ehrenamtliches Team',
                      'partner' => 'Partner',
                      'issuer' => 'Auftraggeber',
                  ];
                  foreach ($page->teams()->split() as $teamTag) {
                      snippet('utilities/badge', [
                          'label' => esc($teamLabels[$teamTag] ?? ucfirst($teamTag)),
                          'theme' => 'team-' . $teamTag,
                          'size' => 'large',
                      ]);
                  }
              } : null,
          ]) ?>
            <?php endslot() ?>
          <?php endsnippet() ?>

          <?php if ($page->hasAnyContactInfo() || $page->address()->isNotEmpty() || $page->hasSocialMedia()) : ?>
                <?php
                $coords = $page->geo()->toObject();
              $hasCoords = $coords->latitude()->isNotEmpty() && $coords->longitude()->isNotEmpty();
              $mapboxToken = mmhMapboxToken();
              $hasMap = $page->address()->isNotEmpty() && $mapboxToken !== '';
              ?>
                <?php snippet('sections/glance', [
              'label' => 'Kontakt',
              'map' => $hasMap ? [
                  // without stored coordinates the map shows the MachMit!Haus
                  'lat' => $hasCoords ? $coords->latitude()->value() : 51.906169,
                  'lng' => $hasCoords ? $coords->longitude()->value() : 10.429327,
                  'token' => $mapboxToken,
                  'label' => $page->name()->value() . ', ' . $page->address()->value(),
                  'aria' => 'Karte: ' . $page->address()->value(),
              ] : null,
            ], slots: true) ?>
                <?php if ($page->email()->isNotEmpty()) : ?>
                    <?php snippet('utilities/fact', ['icon' => 'mail', 'label' => 'E-Mail', 'value' => esc($page->email()->value()), 'href' => 'mailto:' . $page->email()->value()]) ?>
                <?php endif ?>
                <?php if ($page->phone()->isNotEmpty()) : ?>
                    <?php snippet('utilities/fact', ['icon' => 'phone', 'label' => 'Telefon', 'value' => esc($page->phone()->value()), 'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $page->phone()->value())]) ?>
                <?php endif ?>
                <?php if ($page->address()->isNotEmpty()) : ?>
                    <?php snippet('utilities/fact', ['icon' => 'map-pin', 'label' => 'Adresse', 'value' => esc($page->address()->value())]) ?>
                <?php endif ?>
                <?php foreach ($page->socialLinks() as $social) : ?>
                    <?php snippet('utilities/fact', ['icon' => $social['icon'], 'label' => esc($social['label']), 'value' => esc($social['value']), 'href' => $social['href'], 'external' => true]) ?>
                <?php endforeach ?>
                <?php endsnippet() ?>
          <?php endif ?>
        </div>
      </div>
      
      <!-- Description Section -->
      <?php if ($page->description()->isNotEmpty()) : ?>
        <div class="grid-item member-description" data-span="1/1">
          <h2>Über <?= $page->name()->html() ?></h2>
          <div class="description-content">
            <?= $page->description()->kirbytext() ?>
          </div>
        </div>
      <?php endif ?>
      
      <!-- Projects Section -->
      <?php if ($memberProjects->count() > 0) : ?>
        <div class="grid-item member-projects" data-span="1/1">
          <?php snippet('utilities/divider') ?>
          <h2>Aktuelle Projekte</h2>
            <?php snippet('utilities/card-list', ['pages' => $memberProjects]) ?>
        </div>
      <?php endif ?>
      
      <!-- Newsletter Section -->
      <?php if ($memberNewsletters->count() > 0) : ?>

        <div class="grid-item member-newsletters" data-span="1/1">
          <?php snippet('utilities/divider') ?>
          <h2>Newsletter-Ausgaben</h2>
            <?php snippet('utilities/card-list', ['pages' => $memberNewsletters]) ?>
        </div>
      <?php endif ?>
      
      <!-- Notes/Blog Articles Section -->
      <?php if ($memberNotes->count() > 0) : ?>
        <div class="grid-item member-notes" data-span="1/1">
          <?php snippet('utilities/divider') ?>
          <h2>Blog-Artikel</h2>
            <?php snippet('utilities/card-list', ['pages' => $memberNotes]) ?>
        </div>
      <?php endif ?>

    </div>
  </section>
</main>

<?php if (!empty($hasMap)) : ?>
    <?= js('assets/js/event-map.js?version=' . filemtime(kirby()->root('index') . '/assets/js/event-map.js')) ?>
<?php endif ?>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
