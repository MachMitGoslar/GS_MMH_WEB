<?php
/**
 * Notes (Tagebuch) Listing Template
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main class="main">
  <!-- Hero Section -->
  <section class="notes-hero">
    <?php if ($cover = $page->cover()->toFile()) : ?>
      <div class="notes-hero-image">
        <img src="<?= $cover->crop(1920, 600)->url() ?>"
             alt="<?= $page->title()->html() ?>"
             loading="eager">
        <div class="notes-hero-overlay"></div>
      </div>
    <?php endif ?>
    <div class="notes-hero-content">
      <div class="grid content">
        <div class="grid-item" data-span="1/1">
          <h1 class="font-titleXXL"><?= $page->title()->html() ?></h1>
          <?php if ($page->subheadline()->isNotEmpty()) : ?>
            <p class="font-titleXL font-weight-light"><?= $page->subheadline()->html() ?></p>
          <?php endif ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Notes Grid -->
  <section class="grid content">
    <?php
    $notes = $page->children()->listed()->sortBy('date', 'desc');
$featuredNotes = $notes->filterBy('featured', true);
$regularNotes = $notes->filterBy('featured', '!=', true);
?>

    <!-- Featured Notes -->
    <?php if ($featuredNotes->count() > 0) : ?>
      <div class="grid-item" data-span="1/1">
        <h2 class="font-title section-title">Hervorgehobene Einträge</h2>
      </div>
      <div class="grid-item" data-span="1/1">
        <ul class="grid notes-grid notes-grid--featured">
          <?php foreach ($featuredNotes as $note) : ?>
            <li class="note-card-wrapper note-card-wrapper--featured">
              <?= snippet('content-types/notes/noteCard', ['note' => $note, 'featured' => true]) ?>
            </li>
          <?php endforeach ?>
        </ul>
      </div>
    <?php endif ?>

    <!-- All Notes -->
    <div class="grid-item" data-span="1/1">
      <h2 class="font-title section-title">
        <?= $featuredNotes->count() > 0 ? 'Alle Einträge' : 'Tagebucheinträge' ?>
      </h2>
    </div>

    <?php if ($regularNotes->count() > 0) : ?>
      <div class="grid-item" data-span="1/1">
        <ul class="grid notes-grid">
          <?php foreach ($regularNotes as $note) : ?>
            <li class="note-card-wrapper">
              <?= snippet('content-types/notes/noteCard', ['note' => $note]) ?>
            </li>
          <?php endforeach ?>
        </ul>
      </div>
    <?php else : ?>
      <div class="grid-item" data-span="1/1">
        <p class="font-body notes-empty">Noch keine Tagebucheinträge vorhanden.</p>
      </div>
    <?php endif ?>
  </section>
</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
