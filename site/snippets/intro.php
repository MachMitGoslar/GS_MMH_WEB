<?php
/*
  Snippets are a great way to store code snippets for reuse
  or to keep your templates clean.

  This intro snippet is reused in multiple templates.
  While it does not contain much code, it helps to keep your
  code DRY and thus facilitate maintenance when you have
  to make changes.

  More about snippets:
  https://getkirby.com/docs/guide/templates/snippets
*/
?>
<header class="bg-white dark:bg-gray-900">
  <h1 class=" text-4xl tracking-tight font-bold text-gray-900 dark:text-white"><?= $page->headline()->or($page->title())->esc() ?></h1>
  <?php if ($page->subheadline()->isNotEmpty()): ?>
  <h2 class="mb-4 text-4xl tracking-tight font-light text-gray-300 dark:text-white"><small><?= $page->subheadline()->esc() ?></small></h2>
  <?php endif ?>
</header>
