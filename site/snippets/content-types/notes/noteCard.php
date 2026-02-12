<?php
/**
 * Note Card Snippet
 * Reusable card component for displaying note teasers
 *
 * @var \Kirby\Cms\Page $note The note page object
 * @var bool $featured Whether this is a featured card (larger layout)
 */

$featured = $featured ?? false;
$authors = $note->author()->toPages();
?>

<article class="note-card <?= $featured ? 'note-card--featured' : '' ?>">
  <?php if ($featured && ($cover = $note->cover())) : ?>
    <!-- Featured Card with Large Image -->
    <div class="note-card-featured">
      <div class="note-card-image-wrapper">
        <a href="<?= $note->url() ?>" class="note-card-image-link">
          <img src="<?= $cover->crop(1200, 500)->url() ?>"
               alt="<?= $note->title()->html() ?>"
               class="note-card-image"
               loading="lazy">
          <div class="note-card-overlay"></div>
        </a>
        <!-- Authors on image border (right side) -->
        <?php if ($authors->count() > 0) : ?>
          <div class="note-card-authors">
            <?php foreach ($authors->limit(2) as $author) : ?>
              <a href="<?= $author->url() ?>" class="note-card-author" title="<?= $author->title()->html() ?>">
                <?php if ($authorImage = $author->cover()) : ?>
                  <img src="<?= $authorImage->crop(48, 48)->url() ?>" alt="<?= $author->title()->html() ?>">
                <?php else : ?>
                  <span class="placeholder-avatar-small"><?= strtoupper(substr($author->title()->value(), 0, 1)) ?></span>
                <?php endif ?>
              </a>
            <?php endforeach ?>
            <?php if ($authors->count() > 2) : ?>
              <span class="note-card-author-more font-footnote">+<?= $authors->count() - 2 ?></span>
            <?php endif ?>
          </div>
        <?php endif ?>
      </div>
      <div class="note-card-content">
        <div class="note-card-meta">
          <time datetime="<?= $note->date()->toDate('c') ?>" class="note-card-date font-footnote">
            <?= $note->published() ?>
          </time>
          <?php if ($note->tags()->isNotEmpty()) : ?>
            <div class="note-card-tags">
              <?php foreach ($note->tags()->split() as $tag) : ?>
                <span class="tag">#<?= $tag ?></span>
              <?php endforeach ?>
            </div>
          <?php endif ?>
        </div>
        <h3 class="note-card-title font-titleXL">
          <a href="<?= $note->url() ?>"><?= $note->title()->html() ?></a>
        </h3>
        <?php if ($note->headline()->isNotEmpty()) : ?>
          <p class="note-card-subtitle font-headline"><?= $note->headline()->html() ?></p>
        <?php endif ?>
        <p class="note-card-excerpt font-body">
          <?= $note->text()->toBlocks()->first()?->text()->excerpt(200) ?? '' ?>
        </p>
        <a href="<?= $note->url() ?>" class="gs-c-btn" data-type="secondary" data-size="small">
          Weiterlesen
        </a>
      </div>
    </div>
  <?php else : ?>
    <!-- Regular Card -->
    <div class="note-card-image-wrapper">
      <?php if ($cover = $note->cover()) : ?>
        <a href="<?= $note->url() ?>" class="note-card-image-link">
          <img src="<?= $cover->crop(600, 400)->url() ?>"
               alt="<?= $note->title()->html() ?>"
               class="note-card-image"
               loading="lazy">
        </a>
      <?php endif ?>
      <!-- Authors on image border (right side) -->
      <?php if ($authors->count() > 0) : ?>
        <div class="note-card-authors">
          <?php foreach ($authors->limit(2) as $author) : ?>
            <a href="<?= $author->url() ?>" class="note-card-author" title="<?= $author->title()->html() ?>">
              <?php if ($authorImage = $author->cover()) : ?>
                <img src="<?= $authorImage->crop(40, 40)->url() ?>" alt="<?= $author->title()->html() ?>">
              <?php else : ?>
                <span class="placeholder-avatar-small"><?= strtoupper(substr($author->title()->value(), 0, 1)) ?></span>
              <?php endif ?>
            </a>
          <?php endforeach ?>
          <?php if ($authors->count() > 2) : ?>
            <span class="note-card-author-more font-footnote">+<?= $authors->count() - 2 ?></span>
          <?php endif ?>
        </div>
      <?php endif ?>
    </div>
    <div class="note-card-content">
      <div class="note-card-meta">
        <time datetime="<?= $note->date()->toDate('c') ?>" class="note-card-date font-footnote">
          <?= $note->published() ?>
        </time>
        <?php if ($note->tags()->isNotEmpty()) : ?>
          <div class="note-card-tags">
            <?php foreach (array_slice($note->tags()->split(), 0, 2) as $tag) : ?>
              <span class="tag">#<?= $tag ?></span>
            <?php endforeach ?>
          </div>
        <?php endif ?>
      </div>
      <h3 class="note-card-title font-headline">
        <a href="<?= $note->url() ?>"><?= $note->title()->html() ?></a>
      </h3>
      <?php if ($note->headline()->isNotEmpty()) : ?>
        <p class="note-card-subtitle font-subheadline"><?= $note->headline()->excerpt(80) ?></p>
      <?php endif ?>
      <p class="note-card-excerpt font-body">
        <?= $note->text()->toBlocks()->first()?->text()->excerpt(120) ?? '' ?>
      </p>
      <a href="<?= $note->url() ?>" class="gs-c-btn" data-type="secondary" data-size="small">
        Weiterlesen
      </a>
    </div>
  <?php endif ?>
</article>
