<?php
/**
 * Button Block Snippet
 *
 * Renders a button with customizable styling and alignment
 */

$buttonText = $block->buttonText()->or('Click Here');
$buttonUrl = $block->buttonUrl();
$buttonStyle = $block->buttonStyle()->or('primary');
$buttonAlignment = $block->buttonAlignment()->or('left');
?>

<section class="grid content mb-7">
  <div class="grid-item" data-span="1/1">
    <div class="button-block button-block--<?= $buttonAlignment ?>">
      <?php if ($buttonUrl->isNotEmpty()) : ?>
        <a href="<?= $buttonUrl->html() ?>" class="gs-c-btn gs-c-btn--<?= $buttonStyle ?>">
            <?= $buttonText->html() ?>
        </a>
      <?php else : ?>
        <button class="gs-c-btn gs-c-btn--<?= $buttonStyle ?>">
          <?= $buttonText->html() ?>
        </button>
      <?php endif ?>
    </div>
  </div>
</section>