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
$cover = $note->cover();
?>

<article class="note-card <?= $featured ? 'note-card--featured' : '' ?>">
  <?php if ($featured) : ?>
    <!-- Featured Card with Large Image -->
    <div class="note-card-featured">
      <div class="note-card-image-wrapper">
        <div class="note-card-image-link">
          <?php if ($cover) : ?>
            <?php snippet('utilities/image', [
                'file' => $cover,
                'role' => 'content',
                'ratio' => '12:5',
                'sizes' => '(min-width: 1200px) 1200px, 100vw',
                'class' => 'note-card-image',
                'alt' => $note->title()->value(),
            ]) ?>
            <div class="note-card-overlay"></div>
          <?php else : ?>
              <?php snippet('utilities/imagePlaceholder', ['class' => 'note-card-image']) ?>
          <?php endif ?>
        </div>
        <!-- Authors on image border (right side) -->
        <?php if ($authors->count() > 0) : ?>
          <div class="note-card-authors avatar-stack" data-reveal>
            <?php $authorIndex = 0; ?>
            <?php foreach ($authors->limit(2) as $author) : ?>
              <a href="<?= $author->url() ?>" class="note-card-author" style="--stack-index: <?= $authorIndex++ ?>" title="<?= $author->title()->html() ?>">
                <?php if ($authorImage = $author->cover()) : ?>
                  <?php $avatar = mmhAvatarImage($authorImage, 96); ?>
                  <img src="<?= $avatar['url'] ?>" data-fit="<?= $avatar['fit'] ?>" alt="<?= $author->title()->html() ?>">
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
          <time datetime="<?= $note->published('c') ?>" class="note-card-date font-footnote">
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
          <?= $note->title()->html() ?>
        </h3>
        <?php if ($note->headline()->isNotEmpty()) : ?>
          <p class="note-card-subtitle font-subheadline font-line-height-narrow"><?= $note->headline()->html() ?></p>
        <?php endif ?>
        <p class="note-card-excerpt font-footnote">
          <?= $note->text()->toBlocks()->first()?->text()->excerpt(200) ?? '' ?>
        </p>
        <a href="<?= $note->url() ?>" class="gs-c-btn" data-type="secondary" data-size="small">
          Weiterlesen
        </a>
      </div>
    </div>
  <?php else : ?>
    <!-- Regular Card -->
    <div class="note-card-image-wrapper note-card-image-wrapper--has-image">
      <div class="note-card-image-link">
        <?php if ($cover) : ?>
          <?php snippet('utilities/image', [
              'file' => $cover,
              'role' => 'card',
              'ratio' => '3:2',
              'sizes' => '(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw',
              'class' => 'note-card-image',
              'alt' => $note->title()->value(),
          ]) ?>
        <?php else : ?>
            <?php snippet('utilities/imagePlaceholder', ['class' => 'note-card-image']) ?>
        <?php endif ?>
      </div>
      <!-- Authors on image border (right side) -->
      <?php if ($authors->count() > 0) : ?>
        <div class="note-card-authors avatar-stack" data-reveal>
            <?php $authorIndex = 0; ?>
            <?php foreach ($authors->limit(2) as $author) : ?>
            <a href="<?= $author->url() ?>" class="note-card-author" style="--stack-index: <?= $authorIndex++ ?>" title="<?= $author->title()->html() ?>">
                <?php if ($authorImage = $author->cover()) : ?>
                <?php $avatar = mmhAvatarImage($authorImage, 80); ?>
                <img src="<?= $avatar['url'] ?>" data-fit="<?= $avatar['fit'] ?>" alt="<?= $author->title()->html() ?>">
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
        <time datetime="<?= $note->published('c') ?>" class="note-card-date font-footnote">
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
      <h3 class="note-card-title font-headline font-line-height-narrow">
        <?= $note->title()->html() ?>
      </h3>
      <?php if ($note->headline()->isNotEmpty()) : ?>
        <p class="note-card-subtitle font-subheadline font-line-height-narrow"><?= $note->headline()->excerpt(80) ?></p>
      <?php endif ?>
      <p class="note-card-excerpt font-footnote">
        <?= $note->text()->toBlocks()->first()?->text()->excerpt(120) ?? '' ?>
      </p>
      <a href="<?= $note->url() ?>" class="gs-c-btn" data-type="secondary" data-size="small">
        Weiterlesen
      </a>
    </div>
  <?php endif ?>
</article>
