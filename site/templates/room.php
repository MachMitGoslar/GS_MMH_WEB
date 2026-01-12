<?php
/**
 * Room Detail Template
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */

$roomsPage = $page->parent();
$allRooms = $roomsPage->children()->listed();
?>
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main class="main">
  <!-- Breadcrumb -->
  <nav class="grid content room-breadcrumb">
    <div class="grid-item" data-span="1/1">
      <a href="<?= $roomsPage->url() ?>" class="breadcrumb-link font-footnote">
        ← Zurück zur Übersicht
      </a>
    </div>
  </nav>

  <!-- Room Header -->
  <section class="grid content room-header">
    <div class="grid-item" data-span="2/3">
      <h1 class="font-titleXXL"><?= $page->title()->html() ?></h1>
      <?php if ($page->short_description()->isNotEmpty()) : ?>
        <p class="font-headline room-short-desc"><?= $page->short_description()->html() ?></p>
      <?php endif ?>
    </div>
    <div class="grid-item" data-span="1/3">
      <div class="room-quick-info">
        <div class="quick-info-item">
          <span class="quick-info-label font-footnote">Kapazität</span>
          <span class="quick-info-value font-headline"><?= $page->capacity()->or('–') ?> Personen</span>
        </div>
        <?php if ($page->area()->isNotEmpty()) : ?>
          <div class="quick-info-item">
            <span class="quick-info-label font-footnote">Fläche</span>
            <span class="quick-info-value font-headline"><?= $page->area() ?> m²</span>
          </div>
        <?php endif ?>
        <?php if ($page->accessible()->toBool()) : ?>
          <div class="quick-info-item quick-info-badge">
            <span class="badge-accessible">♿ Barrierefrei</span>
          </div>
        <?php endif ?>
      </div>
    </div>
  </section>

  <!-- Gallery -->
  <section class="grid content room-gallery-section">
    <div class="grid-item" data-span="1/1">
      <?php if ($cover = $page->cover()->toFile()) : ?>
        <div class="room-main-image">
          <img src="<?= $cover->crop(1200, 600)->url() ?>"
               alt="<?= $page->title()->html() ?>"
               loading="eager">
        </div>
      <?php endif ?>

      <?php $gallery = $page->gallery()->toFiles(); ?>
      <?php if ($gallery->count() > 0) : ?>
        <div class="room-gallery">
          <?php foreach ($gallery as $image) : ?>
            <a href="<?= $image->url() ?>" class="room-gallery-item" data-lightbox="room-gallery">
              <img src="<?= $image->crop(300, 200)->url() ?>"
                   alt="<?= $image->alt()->or($page->title()) ?>"
                   loading="lazy">
            </a>
          <?php endforeach ?>
        </div>
      <?php endif ?>

      <?php if ($page->virtual_tour_url()->isNotEmpty()) : ?>
        <div class="room-virtual-tour">
          <a href="<?= $page->virtual_tour_url() ?>" target="_blank" rel="noopener" class="gs-c-btn" data-type="secondary">
            🔭 360°-Tour starten
          </a>
        </div>
      <?php endif ?>
    </div>
  </section>

  <!-- Content & Sidebar -->
  <section class="grid content room-content-section">
    <!-- Main Content -->
    <div class="grid-item" data-span="2/3">
      <!-- Description -->
      <?php if ($page->description()->isNotEmpty()) : ?>
        <div class="room-description">
          <h2 class="font-title">Beschreibung</h2>
          <?php foreach ($page->description()->toBlocks() as $block) : ?>
            <div class="c-blog c-blog-<?= $block->type() ?>">
              <?= $block ?>
            </div>
          <?php endforeach ?>
        </div>
      <?php endif ?>

      <!-- Equipment -->
      <?php $equipment = $page->equipment()->toStructure(); ?>
      <?php if ($equipment->count() > 0) : ?>
        <div class="room-equipment">
          <h2 class="font-title">Ausstattung</h2>
          <ul class="equipment-list">
            <?php foreach ($equipment as $item) : ?>
              <li class="equipment-item <?= $item->included()->toBool() ? '' : 'equipment-extra' ?>">
                <span class="equipment-icon" data-icon="<?= $item->icon()->value() ?>">
                  <?= snippet('content-types/rooms/equipmentIcon', ['icon' => $item->icon()->value()]) ?>
                </span>
                <span class="equipment-name font-body"><?= $item->name()->html() ?></span>
                <?php if (! $item->included()->toBool()) : ?>
                  <span class="equipment-extra-label font-footnote">Gegen Aufpreis</span>
                <?php endif ?>
              </li>
            <?php endforeach ?>
          </ul>
        </div>
      <?php endif ?>

      <!-- Pricing (if enabled) -->
      <?php if ($page->pricing_enabled()->toBool()) : ?>
        <div class="room-pricing">
          <h2 class="font-title">Preise</h2>
          <div class="pricing-table">
            <?php if ($page->hourly_rate()->isNotEmpty()) : ?>
              <div class="pricing-row">
                <span class="pricing-label font-body">Stundenpreis</span>
                <span class="pricing-value font-headline"><?= number_format($page->hourly_rate()->toFloat(), 2, ',', '.') ?> €</span>
              </div>
            <?php endif ?>
            <?php if ($page->half_day_rate()->isNotEmpty()) : ?>
              <div class="pricing-row">
                <span class="pricing-label font-body">Halbtag (bis 4 Std.)</span>
                <span class="pricing-value font-headline"><?= number_format($page->half_day_rate()->toFloat(), 2, ',', '.') ?> €</span>
              </div>
            <?php endif ?>
            <?php if ($page->full_day_rate()->isNotEmpty()) : ?>
              <div class="pricing-row">
                <span class="pricing-label font-body">Ganzer Tag</span>
                <span class="pricing-value font-headline"><?= number_format($page->full_day_rate()->toFloat(), 2, ',', '.') ?> €</span>
              </div>
            <?php endif ?>
          </div>
          <?php if ($page->nonprofit_discount()->toBool()) : ?>
            <p class="pricing-discount font-footnote">
              💡 <?= $page->nonprofit_discount_percent()->or(0) ?>% Rabatt für gemeinnützige Organisationen
            </p>
          <?php endif ?>
          <?php if ($page->pricing_notes()->isNotEmpty()) : ?>
            <p class="pricing-notes font-footnote"><?= $page->pricing_notes()->html() ?></p>
          <?php endif ?>
        </div>
      <?php endif ?>

      <!-- Availability Calendar -->
      <?php if ($page->show_calendar()->toBool() && $page->calendar_embed_url()->isNotEmpty()) : ?>
        <div class="room-calendar">
          <h2 class="font-title">Verfügbarkeit</h2>
          <div class="calendar-embed">
            <iframe src="<?= $page->calendar_embed_url() ?>"
                    style="border: 0"
                    width="100%"
                    height="400"
                    frameborder="0"
                    scrolling="no"
                    loading="lazy"></iframe>
          </div>
          <p class="font-footnote calendar-note">
            Grau hinterlegte Zeiten sind bereits belegt.
          </p>
        </div>
      <?php endif ?>
    </div>

    <!-- Sidebar -->
    <aside class="grid-item room-sidebar" data-span="1/3">
      <!-- Booking CTA -->
      <div class="sidebar-card sidebar-booking">
        <h3 class="font-headline">Raum anfragen</h3>
        <p class="font-body">Interessiert an diesem Raum?</p>
        <a href="<?= $roomsPage->url() ?>#booking-form" class="gs-c-btn" data-type="primary" data-size="regular">
          Anfrage stellen
        </a>
      </div>

      <!-- Other Rooms -->
      <?php $otherRooms = $allRooms->not($page)->limit(3); ?>
      <?php if ($otherRooms->count() > 0) : ?>
        <div class="sidebar-card sidebar-other-rooms">
          <h3 class="font-headline">Weitere Räume</h3>
          <ul class="other-rooms-list">
            <?php foreach ($otherRooms as $otherRoom) : ?>
              <li class="other-room-item">
                <a href="<?= $otherRoom->url() ?>" class="other-room-link">
                  <?php if ($otherCover = $otherRoom->cover()->toFile()) : ?>
                    <img src="<?= $otherCover->crop(80, 60)->url() ?>"
                         alt="<?= $otherRoom->title()->html() ?>"
                         class="other-room-thumb">
                  <?php endif ?>
                  <div class="other-room-info">
                    <span class="font-body"><?= $otherRoom->title()->html() ?></span>
                    <span class="font-footnote"><?= $otherRoom->capacity() ?> Personen</span>
                  </div>
                </a>
              </li>
            <?php endforeach ?>
          </ul>
        </div>
      <?php endif ?>

      <!-- Contact -->
      <div class="sidebar-card sidebar-contact">
        <h3 class="font-headline">Kontakt</h3>
        <p class="font-body">
          <strong>MachMit!Haus</strong><br>
          Markt 7<br>
          38640 Goslar
        </p>
        <p class="font-body">
          <a href="mailto:machmit@goslar.de">machmit@goslar.de</a><br>
          <a href="tel:+495321704525">05321 704 525</a>
        </p>
      </div>
    </aside>
  </section>
</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
