<?php
/**
 * Button Block Snippet
 *
 * Renders a button with customizable styling and alignment
 */

$buttonText = $block->linktext()->or('Click Here');
$buttonUrl = $block->link();
$buttonTypeData = $block->content()->data()['buttontype'];
$target = $block->target()->toBool();


// Extract button style data safely
$color = $buttonTypeData['color'] ?? 'primary';
$size = $buttonTypeData['size'] ?? 'regular';
// Map 'normal' to 'regular' for backwards compatibility
if ($size === 'normal') {
    $size = 'regular';
}
$style = $buttonTypeData['style'];

?>

<section class="grid content mb-7">
  <div class="grid-item" data-span="1/1">
    <div class="button-block">
      <?php if ($buttonUrl) : ?>
        <a href="<?= $buttonUrl ?>" 
           class="gs-c-btn" 
           data-type="<?= $color ?>"
           data-size="<?= $size ?>"
           data-style="<?= $style ?>"
           <?= $target ? 'target="_blank" rel="noopener"' : '' ?>>
            <?= $buttonText ?>
        </a>
      <?php else : ?>
        <button class="gs-c-btn" 
                data-type="<?= $color ?>"
                data-size="<?= $size ?>"
                data-style="<?= $style ?>">
          <?= $buttonText ?>
        </button>
      <?php endif ?>
    </div>
  </div>
</section>