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
  <section class="grid content mb-12">
    
    <!-- Header with Title and Description -->
    <div class="grid-item mb-8" data-span="1/1">
      <div class="max-w-3xl">
        <h1 class="font-titleXXL mb-4">
          <?= $page->headline()->or($page->title())->html() ?>
        </h1>
        
        <?php if ($page->description()->isNotEmpty()) : ?>
          <p class="font-body text-lg text-gray-600">
            <?= $page->description()->html() ?>
          </p>
        <?php endif ?>
      </div>
    </div>

    <!-- Newsletter Grid -->
    <?php if ($page->children()->listed()->count() > 0) : ?>
      <div class="grid-item" data-span="1/1">
        <h2 class="font-titleL mb-6">Alle Newsletter-Ausgaben</h2>
        
        <ul class="grid content">
          <?php
            $newsletters = $page->children()->listed();
        // Sort by publish_date, then fallback to published date, modified date, or folder number
        $sortedNewsletters = $newsletters->sortBy(function ($newsletter) {
            // Primary: use publish_date field if available
            if ($newsletter->publish_date()->isNotEmpty()) {
                return $newsletter->publish_date()->toTimestamp();
            }
            // Secondary: use published date if available
            $published = $newsletter->published();
            if ($published && ! $published->isEmpty()) {
                return $published->toTimestamp();
            }
            // Tertiary: use modified date
            $modified = $newsletter->modified();
            if ($modified) {
                return $modified->toTimestamp();
            }

            // Final fallback: reverse folder number for manual ordering
            return -intval($newsletter->num());
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
        <div class="text-center py-12 bg-gray-50 rounded-lg">
          <p class="font-body text-gray-600 text-lg">
            Noch keine Newsletter veröffentlicht.
          </p>
        </div>
      </div>
    <?php endif ?>

    <!-- Newsletter Subscription CTA -->
    <div class="grid-item mt-12" data-span="1/1">
      <div class="bg-blue-50 rounded-lg p-8 text-center">
        <h3 class="font-titleL mb-4">Newsletter abonnieren</h3>
        <p class="font-body text-gray-700 mb-6 max-w-2xl mx-auto">
          Bleiben Sie auf dem Laufenden über unsere Projekte, Veranstaltungen und Neuigkeiten. 
          Unser Newsletter erscheint regelmäßig mit spannenden Einblicken in unsere Arbeit.
        </p>
        <a href="mailto:machmit@goslar.de?subject=Newsletter abonnieren" 
           class="gs-c-btn gs-c-btn--primary">
          Jetzt abonnieren
        </a>
      </div>
    </div>
    
  </section>

</main>

<?php snippet('layout/footer'); ?>