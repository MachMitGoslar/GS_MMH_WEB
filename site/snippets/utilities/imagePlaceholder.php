<?php

/**
 * Image Placeholder Snippet
 * Branded stand-in for cards without a cover image
 *
 * @var string|null $class Additional CSS class for the wrapper
 */
?>
<span class="c-image-placeholder<?= isset($class) ? ' ' . esc($class) : '' ?>" aria-hidden="true">
  <img src="<?= url('assets/svg/machmit-logo.svg') ?>" alt="" loading="lazy">
</span>
