<?php /** @var \Kirby\Cms\Block $block */ ?>
<blockquote>
  <?= $block->text()->kirbytext() ?>
  <?php if ($block->citation()->isNotEmpty()) : ?>
  <footer class="font-footnote">
        <?= $block->citation()->kirbytext() ?>
  </footer>
  <?php endif ?>
</blockquote>