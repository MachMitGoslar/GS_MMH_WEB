<?php
/**
 * Note (Tagebucheintrag) Detail Template
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main class="main">
  <article class="note-article">

    <!-- Hero Section with Cover Image -->
    <?php if ($cover = $page->cover()) : ?>
      <section class="note-hero">
        <div class="note-hero-image">
          <img src="<?= $cover->crop(1920, 800)->url() ?>"
               alt="<?= $page->title()->html() ?>"
               loading="eager">
          <div class="note-hero-overlay"></div>
        </div>
        <div class="note-hero-content">
          <div class="grid content">
            <div class="grid-item" data-span="1/1">
              <div class="note-meta-hero">
                <time datetime="<?= $page->date()->toDate('c') ?>" class="note-date">
                  <?= $page->published() ?>
                </time>
                <?php if ($page->tags()->isNotEmpty()) : ?>
                  <div class="note-tags">
                    <?php foreach ($page->tags()->split() as $tag) : ?>
                      <span class="tag">#<?= $tag ?></span>
                    <?php endforeach ?>
                  </div>
                <?php endif ?>
              </div>
              <h1 class="font-titleXXL note-title"><?= $page->title()->html() ?></h1>
              <?php if ($page->headline()->isNotEmpty()) : ?>
                <h2 class="font-titleXL font-weight-light note-subtitle"><?= $page->headline()->html() ?></h2>
              <?php endif ?>
            </div>
          </div>
        </div>
      </section>
    <?php else : ?>
      <!-- No Cover Image - Simple Header -->
      <section class="note-header grid content">
        <div class="grid-item" data-span="1/1">
          <div class="note-meta">
            <time datetime="<?= $page->date()->toDate('c') ?>" class="note-date">
              <?= $page->published() ?>
            </time>
            <?php if ($page->tags()->isNotEmpty()) : ?>
              <div class="note-tags">
                <?php foreach ($page->tags()->split() as $tag) : ?>
                  <span class="tag">#<?= $tag ?></span>
                <?php endforeach ?>
              </div>
            <?php endif ?>
          </div>
          <h1 class="font-titleXXL"><?= $page->title()->html() ?></h1>
          <?php if ($page->headline()->isNotEmpty()) : ?>
            <h2 class="font-titleXL font-weight-light"><?= $page->headline()->html() ?></h2>
          <?php endif ?>
        </div>
      </section>
    <?php endif ?>

    <!-- Author Section -->
    <?php
    $authors = $page->author()->toPages();
if ($authors->count() > 0) :
    ?>
      <section class="note-authors grid content">
        <div class="grid-item" data-span="1/1">
          <div class="authors-list">
            <?php foreach ($authors as $author) : ?>
              <a href="<?= $author->url() ?>" class="author-card">
                <div class="author-avatar">
                  <?php if ($authorImage = $author->cover()) : ?>
                    <img src="<?= $authorImage->crop(80, 80)->url() ?>" alt="<?= $author->title()->html() ?>">
                  <?php else : ?>
                    <div class="placeholder-avatar">
                      <span><?= strtoupper(substr($author->title()->value(), 0, 1)) ?></span>
                    </div>
                  <?php endif ?>
                </div>
                <div class="author-info">
                  <span class="author-name font-headline"><?= $author->title()->html() ?></span>
                  <?php if ($author->role()->isNotEmpty()) : ?>
                    <span class="author-role font-footnote"><?= $author->role()->html() ?></span>
                  <?php endif ?>
                </div>
              </a>
            <?php endforeach ?>
          </div>
        </div>
      </section>
    <?php endif ?>

    <!-- Main Content -->
    <section class="note-content grid content">
      <div class="grid-item" data-span="2/3">
        <div class="note-body">
          <?php foreach ($page->text()->toBlocks() as $block) : ?>
            <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
              <?= $block ?>
            </div>
          <?php endforeach ?>
        </div>
      </div>

      <!-- Sidebar -->
      <aside class="grid-item note-sidebar" data-span="1/3">
        <!-- Share Section -->
        <div class="sidebar-section">
          <h4 class="font-subheadline sidebar-title">Teilen</h4>
          <div class="share-buttons">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($page->url()) ?>"
               target="_blank"
               rel="noopener"
               class="share-btn share-facebook"
               title="Auf Facebook teilen">
              Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($page->url()) ?>&text=<?= urlencode($page->title()->value()) ?>"
               target="_blank"
               rel="noopener"
               class="share-btn share-twitter"
               title="Auf Twitter teilen">
              Twitter
            </a>
            <a href="mailto:?subject=<?= urlencode($page->title()->value()) ?>&body=<?= urlencode($page->url()) ?>"
               class="share-btn share-email"
               title="Per E-Mail teilen">
              E-Mail
            </a>
          </div>
        </div>

        <!-- Tags Section -->
        <?php if ($page->tags()->isNotEmpty()) : ?>
          <div class="sidebar-section">
            <h4 class="font-subheadline sidebar-title">Schlagworte</h4>
            <div class="sidebar-tags">
              <?php foreach ($page->tags()->split() as $tag) : ?>
                <span class="sidebar-tag"><?= $tag ?></span>
              <?php endforeach ?>
            </div>
          </div>
        <?php endif ?>

        <!-- Related Notes -->
        <?php
        $relatedNotes = $page->siblings(false)->listed()->shuffle()->limit(3);
if ($relatedNotes->count() > 0) :
    ?>
          <div class="sidebar-section">
            <h4 class="font-subheadline sidebar-title">Weitere Einträge</h4>
            <div class="related-notes">
              <?php foreach ($relatedNotes as $related) : ?>
                <a href="<?= $related->url() ?>" class="related-note-link">
                  <span class="related-note-title font-body"><?= $related->title()->html() ?></span>
                  <span class="related-note-date font-footnote"><?= $related->published() ?></span>
                </a>
              <?php endforeach ?>
            </div>
          </div>
        <?php endif ?>
      </aside>
    </section>

    <!-- Navigation -->
    <section class="note-navigation grid content">
      <div class="grid-item" data-span="1/1">
        <div class="note-nav-links">
          <?php if ($prev = $page->prev()) : ?>
            <a href="<?= $prev->url() ?>" class="note-nav-link note-nav-prev">
              <span class="nav-label font-footnote">&larr; Vorheriger Eintrag</span>
              <span class="nav-title font-body"><?= $prev->title()->html() ?></span>
            </a>
          <?php else : ?>
            <div class="note-nav-link note-nav-placeholder"></div>
          <?php endif ?>

          <a href="<?= $page->parent()->url() ?>" class="note-nav-link note-nav-all">
            <span class="nav-label font-footnote">Alle Einträge</span>
            <span class="nav-title font-body">Zurück zum Tagebuch</span>
          </a>

          <?php if ($next = $page->next()) : ?>
            <a href="<?= $next->url() ?>" class="note-nav-link note-nav-next">
              <span class="nav-label font-footnote">Nächster Eintrag &rarr;</span>
              <span class="nav-title font-body"><?= $next->title()->html() ?></span>
            </a>
          <?php else : ?>
            <div class="note-nav-link note-nav-placeholder"></div>
          <?php endif ?>
        </div>
      </div>
    </section>

  </article>
</main>

<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
