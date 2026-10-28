
<?php
$question = $block->question()->isNotEmpty() ? $block->question() : $block->summary();
$answer = $block->answer()->isNotEmpty() ? $block->answer() : $block->details();
?>
<?php if ($question->isNotEmpty()) :
    ?>
  <details class="accordion-details">
    <summary class="accordion-summary"><?= $question ?></summary>
    <?php if ($answer->isNotEmpty()) :
        ?>
      <div class="accordion-text font-caption"><?= $answer->kirbytext() ?></div>
        <?php
    endif; ?>
  </details>
    <?php
endif; ?>
