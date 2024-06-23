<?php
/*
  Snippets are a great way to store code snippets for reuse
  or to keep your templates clean.

  In this snippet the svg() helper is a great way to embed SVG
  code directly in your HTML. Pass the path to your SVG
  file to load it.

  More about snippets:
  https://getkirby.com/docs/guide/templates/snippets
*/
?>
<span class="social block inline-flex">
  <a href="https://mastodon.social/@getkirby" aria-label="Follow us on Mastodon" class="px-2 py-1 hover:text-gold">
    <?= svg('assets/icons/mastodon.svg') ?>
  </a>
  <a href="https://instagram.com/getkirby" aria-label="Follow us on Instagram" class="px-2 py-1 hover:text-gold">
    <?= svg('assets/icons/instagram.svg') ?>
  </a>
  <a href="https://youtube.com/kirbycasts" aria-label="Watch our videos on YouTube" class="px-2 py-1 hover:text-gold">
    <?= svg('assets/icons/youtube.svg') ?>
  </a>
  <a href="https://chat.getkirby.com" aria-label="Chat with us on Discord" class="px-2 py-1 hover:text-gold">
    <?= svg('assets/icons/discord.svg') ?>
  </a>
</span>
