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

    <div class="mb-4">
      <?= snippet('sections/hero', ['placeholder' => true]) ?>
    </div>

    <section class="grid content mb-5">
      <div class="grid-item" data-span="1/1">
        <?php snippet('utilities/page-title', [
            'title' => $page->title()->html(),
            'subtitle' => $page->headline()->isNotEmpty() ? $page->headline()->html() : null,
            'before' => function () use ($page) {
                ?>
                <?php snippet('utilities/meta-line', slots: true) ?>
                  <?php slot('start') ?>
                    <?php foreach ($page->tags()->split() as $tag) : ?>
                        <?php snippet('utilities/chip', ['label' => esc($tag), 'theme' => 'tag']) ?>
                    <?php endforeach ?>
                  <?php endslot() ?>
                  <?php slot('end') ?>
                    <time datetime="<?= $page->published('c') ?>" class="font-footnote"><?= $page->published() ?></time>
                  <?php endslot() ?>
                <?php endsnippet() ?>
                <?php
            },
        ]) ?>
      </div>
    </section>

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
                  <?php snippet('utilities/avatar', ['person' => $author, 'name' => $author->title()->value(), 'size' => 160]) ?>
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
          <?php snippet('utilities/blocks', ['blocks' => $page->text()->toBlocks()]) ?>
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
