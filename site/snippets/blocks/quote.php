<blockquote class="p-4 my-4 border-s-4 border-gold-300 bg-gray-50 dark:border-gold-500 dark:bg-gray-800">
  <h2 class="text-xl italic font-medium leading-relaxed text-gray-900 dark:text-white"> <?= $block->text() ?> </h2>

  <?php if ($block->citation()->isNotEmpty()): ?>
  <footer>
    <p> <?= $block->citation() ?> </p>
  </footer>
  <?php endif ?>
</blockquote>