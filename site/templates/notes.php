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
  <div class="mb-4">
    <?= snippet('sections/hero') ?>
  </div>

  <section class="grid content mb-7">
    <div class="grid-item" data-span="1/1">
        <?php snippet('utilities/page-title', [
            'title' => $page->title()->html(),
            'subtitle' => $page->subheadline()->isNotEmpty() ? $page->subheadline()->html() : null,
        ]) ?>
    </div>
  </section>

  <!-- Notes Grid -->
  <section class="grid content">
    <?php
    $notes = $page->children()->listed()->sortBy(function ($note) {
        return $note->publishedTimestamp();
    }, 'desc');
    $featuredNotes = $notes->filterBy('featured', true);
    $regularNotes = $notes->filterBy('featured', '!=', true);
    ?>

    <!-- Featured Notes -->
    <?php if ($featuredNotes->count() > 0) : ?>
      <div class="grid-item" data-span="1/1">
        <h2 class="font-title section-title">Im Fokus</h2>
      </div>
      <div class="grid-item" data-span="1/1">
        <?php snippet('utilities/card-list', ['pages' => $featuredNotes, 'columns' => 1, 'featured' => 'all']) ?>
      </div>
    <?php endif ?>

    <!-- All Notes -->
    <div class="grid-item" data-span="1/1">
      <h4 class="font-headline section-title">
        <?= $featuredNotes->count() > 0 ? 'Alle Einträge' : 'Tagebucheinträge' ?>
      </h4>
    </div>

    <?php if ($regularNotes->count() > 0) : ?>
      <div class="grid-item" data-span="1/1">
        <?php snippet('utilities/card-list', ['pages' => $regularNotes]) ?>
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
