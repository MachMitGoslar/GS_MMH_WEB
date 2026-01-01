<?php
/**
 * Timeline Block Snippet
 *
 * Renders a timeline with alternating left/right layout or constrained layout
 */

$title = $block->title()->or('Timeline');
$entries = $block->entries()->toStructure();
$layout = $block->layout()->or('standard'); // 'standard' or 'constrained'
?>

<?php if ($entries->isNotEmpty()) : ?>
<section class="grid content mb-7">
  <div class="grid-item" data-span="1/1">
    <?php if ($title->isNotEmpty()) : ?>
      <h2 class="font-title mb-3"><?= $title->html() ?></h2>
    <?php endif ?>
    
    <div class="timeline-container<?= $layout === 'constrained' ? ' timeline-container--constrained' : '' ?>">
      <?php if ($layout === 'constrained') : ?>
            <?php foreach ($entries as $entry) : ?>
        <div class="timeline-item">
          <div class="timeline-connector"></div>
          <div class="timeline-item__container">
            <div class="timeline-content">
              <div class="timeline-date"><?= $entry->year()->html() ?></div>
              <h4 class="timeline-title">Timeline Entry</h4>
              <div class="timeline-text"><?= $entry->summary()->html() ?></div>
                <?php if ($entry->image()->isNotEmpty() && $imageFile = $entry->image()->toFile()) : ?>
                <div class="timeline-image">
                  <img src="<?= $imageFile->url() ?>" alt="<?= $entry->year()->html() ?>" loading="lazy">
                </div>
                <?php endif ?>
            </div>
          </div>
        </div>
            <?php endforeach ?>
      <?php else : ?>
          <?php
            $isLeft = true;
          foreach ($entries as $entry) :
              ?>
          <div class="timeline-item <?= $isLeft ? 'timeline-item--left' : 'timeline-item--right' ?>">
                <?php if ($isLeft) : ?>
              <div class="timeline-item__container">
                <!-- Left side: Text | Image | Connector -->
                <div class="timeline-content">
                  <div class="font-headline timeline-date"><?= $entry->year()->html() ?></div>
                  <div class="font-body timeline-text"><?= $entry->summary()->html() ?></div>
                </div>
                <div class="timeline-image">
                    <?php if ($entry->image()->isNotEmpty() && $imageFile = $entry->image()->toFile()) : ?>
                    <img src="<?= $imageFile->url() ?>" alt="<?= $entry->year()->html() ?>" loading="lazy">
                    <?php endif ?>
                </div>
                <div class="timeline-connector"></div>
              </div>
                <?php else : ?>
              <!-- Right side: Connector | Image | Text -->
              <div class="timeline-item__container">
                <div class="timeline-connector"></div>
                <div class="timeline-image">
                    <?php if ($entry->image()->isNotEmpty() && $imageFile = $entry->image()->toFile()) : ?>
                    <img src="<?= $imageFile->url() ?>" alt="<?= $entry->year()->html() ?>" loading="lazy">
                    <?php endif ?>
                </div>
                <div class="timeline-content">
                  <div class="font-headline timeline-date"><?= $entry->year()->html() ?></div>
                  <div class="font-body timeline-text"><?= $entry->summary()->html() ?></div>
                </div>
              </div>
                <?php endif ?>
          </div>
                <?php
              $isLeft = ! $isLeft;
          endforeach;
?>
      <?php endif ?>
    </div>
  </div>
</section>
<?php endif ?>