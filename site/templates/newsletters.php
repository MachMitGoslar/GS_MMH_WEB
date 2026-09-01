<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>

<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main class="main">

  <!-- Hero Section -->
  <div class="mb-7">
    <?= snippet('sections/hero') ?>
  </div>

  <!-- Content Section -->
  <section class="grid content mb-7">
    
    <!-- Header with Title and Description -->
    <div class="grid-item mb-6" data-span="1/1">
      <div class="newsletter-listing__intro">
        <h1 class="font-titleXXL mb-4">
          <?= $page->headline()->or($page->title())->html() ?>
        </h1>
        
        <?php if ($page->description()->isNotEmpty()) : ?>
          <p class="font-body">
            <?= $page->description()->html() ?>
          </p>
        <?php endif ?>
      </div>
    </div>

    <!-- Newsletter Grid -->
    <?php if ($page->children()->listed()->count() > 0) : ?>
      <div class="grid-item" data-span="1/1">
        <h2 class="font-title mb-6">Alle Newsletter-Ausgaben</h2>
        
        <ul class="grid content">
          <?php
            $newsletters = $page->children()->listed();
        // Sort by publish_date, then fallback to published date, modified date, or folder number
        $sortedNewsletters = $newsletters->sortBy(function ($newsletter) {
            // Primary: use publish_date field if available
            if ($newsletter->publish_date()->isNotEmpty()) {
                return mmhTimestampValue($newsletter->publish_date());
            }
            // Secondary: use published date if available
            $published = $newsletter->published();
            if ($published && !$published->isEmpty()) {
                return mmhTimestampValue($published);
            }
            // Tertiary: use modified date
            $modified = $newsletter->modified();
            if ($modified) {
                return mmhTimestampValue($modified);
            }

            // Final fallback: reverse folder number for manual ordering
            return -(int) ($newsletter->num());
        }, 'desc');

        foreach ($sortedNewsletters as $index => $newsletter) :
            ?>
                <?= snippet('content-types/newsletter/newsletterItem', [
            'newsletter' => $newsletter,
            'class' => $index === 0 ? 'newsletter-item--featured' : '',
              ]) ?>
          <?php endforeach ?>
        </ul>
      </div>
    <?php else : ?>
      <div class="grid-item" data-span="1/1">
        <div class="newsletter-empty">
          <p class="font-body">
            Noch keine Newsletter veröffentlicht.
          </p>
        </div>
      </div>
    <?php endif ?>

    <!-- Newsletter Subscription CTA -->
    <div class="grid-item mt-7" data-span="1/1">
      <div class="newsletter-cta">
        <h3 class="font-title mb-4">Newsletter abonnieren</h3>
        <p class="font-body newsletter-cta__text mb-6">
          Bleiben Sie auf dem Laufenden über unsere Projekte, Veranstaltungen und Neuigkeiten. 
          Unser Newsletter erscheint regelmäßig mit spannenden Einblicken in unsere Arbeit.
        </p>
        <button class="gs-c-btn newsletter-subscribe-open" data-type="primary" data-size="regular" data-style="pill" type="button" aria-haspopup="dialog" aria-controls="newsletter-subscribe-modal">
          Jetzt abonnieren
        </button>
      </div>
    </div>
    
  </section>

  <?= snippet('content-types/newsletter/newsletterTeaser', ['showTeaser' => false]) ?>

</main>

<?php snippet('layout/footer'); ?>
