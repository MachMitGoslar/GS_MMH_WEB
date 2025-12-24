<?php
/**
 * Newsletter Blog Entries Snippet
 * @var \Kirby\Content\Field $entries
 */
?>
<div class="blog-entries">
  <?php foreach($entries->toStructure() as $entry): ?>
  <article class="blog-entry layout-<?= $entry->layout()->or('imageLeft') ?>">
    
    <div class="entry-content">
      <?php if($entry->layout()->value() === 'imageLeft'): ?>
        <!-- Image Left Layout -->
        <div class="entry-media">
          <?php if($image = $entry->image()->toFile()): ?>
            <img src="<?= $image->url() ?>" alt="<?= $entry->headline()->or($image->alt()) ?>" class="entry-image">
          <?php endif ?>
        </div>
        <div class="entry-text">
          <header class="entry-header">
            <h3 class="entry-headline font-titleM"><?= $entry->headline()->kt() ?></h3>
            <?php if($entry->subheadline()->isNotEmpty()): ?>
              <h4 class="entry-subheadline font-titleS"><?= $entry->subheadline()->kt() ?></h4>
            <?php endif ?>
          </header>
          <div class="entry-body">
            <?= $entry->content_text()->kt() ?>
          </div>
          <?php if($entry->link()->isNotEmpty() || $entry->mailto()->isNotEmpty()): ?>
          <footer class="entry-footer">
            <?php if($entry->link()->isNotEmpty()): ?>
              <a href="<?= $entry->link() ?>" class="entry-link" target="_blank" rel="noopener">
                Mehr erfahren →
              </a>
            <?php endif ?>
            <?php if($entry->mailto()->isNotEmpty()): ?>
              <a href="mailto:<?= $entry->mailto() ?>" class="entry-email">
                Kontakt per E-Mail →
              </a>
            <?php endif ?>
          </footer>
          <?php endif ?>
        </div>
      
      <?php else: ?>
        <!-- Image Right Layout -->
        <div class="entry-text">
          <header class="entry-header">
            <h3 class="entry-headline font-titleM"><?= $entry->headline()->kt() ?></h3>
            <?php if($entry->subheadline()->isNotEmpty()): ?>
              <h4 class="entry-subheadline font-titleS"><?= $entry->subheadline()->kt() ?></h4>
            <?php endif ?>
          </header>
          <div class="entry-body">
            <?= $entry->content_text()->kt() ?>
          </div>
          <?php if($entry->link()->isNotEmpty() || $entry->mailto()->isNotEmpty()): ?>
          <footer class="entry-footer">
            <?php if($entry->link()->isNotEmpty()): ?>
              <a href="<?= $entry->link() ?>" class="entry-link" target="_blank" rel="noopener">
                Mehr erfahren →
              </a>
            <?php endif ?>
            <?php if($entry->mailto()->isNotEmpty()): ?>
              <a href="mailto:<?= $entry->mailto() ?>" class="entry-email">
                Kontakt per E-Mail →
              </a>
            <?php endif ?>
          </footer>
          <?php endif ?>
        </div>
        <div class="entry-media">
          <?php if($image = $entry->image()->toFile()): ?>
            <img src="<?= $image->url() ?>" alt="<?= $entry->headline()->or($image->alt()) ?>" class="entry-image">
          <?php endif ?>
        </div>
      <?php endif ?>
    </div>
    
  </article>
  <?php endforeach ?>
</div>