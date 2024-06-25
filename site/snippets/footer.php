<?php
/*
  Snippets are a great way to store code snippets for reuse
  or to keep your templates clean.

  This footer snippet is reused in all templates.

  More about snippets:
  https://getkirby.com/docs/guide/templates/snippets
*/
?>
  </main>

  <?php if($footer->isNotEmpty()): ?>
  <footer class="footer static bottom-0 w-full">
    <div class="grid md:grid-cols-12 grid-cols-4 gap-4 p-3 max-w-screen-xl mx-auto ">
      <div class="col-span-8">
        <h2><?= $footer->headline_main()->esc() ?></h2>
        <p class="text-sm dark:text-gray-300 text-gray-300">
        <?= $footer->text()->esc() ?>
      </p>
      <?php snippet('social') ?>
      </div>
      <div class="col-span-2">
        <h2 class=" "><?=$footer->headline_pages()->esc()?></h2>
        <ul>
          <?php foreach ($footer->pages()->toPages() as $page): ?>
            <li><a href="<?= $page->url() ?>"><?= $page->title()->esc() ?></a></li>
          <?php endforeach ?>
        </ul>
      </div>
      <div class="col-span-2">
        <h2><?= $footer->headline_links()->esc()?></h2>
        <ul>
          <?php foreach($footer->links()->toStructure() as $link): ?>
            <li><a href="<?=$link->link()->url()->esc('html')?>"> <?=$link->linkText()->esc()?></a></li>
          <?php endforeach ?>
        </ul>
       
      </div>
    </div>
  </footer>
  <?php endif ?>

  <?= js([
    'assets/js/prism.js',
    'assets/js/lightbox.js',
    'assets/js/index.js',
    '@auto'
  ]) ?>

</body>
</html>
