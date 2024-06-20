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

  <footer class="footer static bottom-0 w-full  bg-gray-500 dark:bg-gray-900">
    <div class="grid md:grid-cols-12 grid-cols-4 gap-4 p-3 max-w-screen-xl mx-auto ">
      <div class="col-span-8">
        <h2><a href="https://getkirby.com">Made with Kirby</a></h2>
        <p class="text-sm dark:text-gray-300 text-gray-700">
          Kirby: the file-based CMS that adapts to any project, loved by developers and editors alike
        </p>
      </div>
      <div class="col-span-2">
        <h2 class=" ">Pages</h2>
        <ul>
          <?php foreach ($site->children()->listed() as $example): ?>
          <li><a href="<?= $example->url() ?>"><?= $example->title()->esc() ?></a></li>
          <?php endforeach ?>
        </ul>
      </div>
      <div class="col-span-2">
        <h2>Kirby</h2>
        <ul>
          <li><a class="text-sm" href="https://getkirby.com">Website</a></li>
          <li><a href="https://getkirby.com/docs">Docs</a></li>
          <li><a href="https://forum.getkirby.com">Forum</a></li>
          <li><a href="https://chat.getkirby.com">Chat</a></li>
          <li><a href="https://github.com/getkirby">GitHub</a></li>
        </ul>
      </div>
    </div>
  </footer>

  <?= js([
    'assets/js/prism.js',
    'assets/js/lightbox.js',
    'assets/js/index.js',
    '@auto'
  ]) ?>

</body>
</html>
