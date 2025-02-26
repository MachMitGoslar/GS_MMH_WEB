<?php
/**
* @var Kirby\Cms\Site $site
* @var Kirby\Cms\Page $page
*/
?>
<div class="c-newsletter-teaser grid-item" data-span="1/2">
  <div class="mb-5">
    <h2 class="font-title2 color-fg-light mb-3"><?=$site->newsletterTeaserHeadline()?></h2>
    <p class="font-subheadline color-fg-light mb-3"><?=$site->newsletterTeaserSubheadline()?></p>
    <p class="font-body color-fg-light"><?=$site->newsletterTeaserText()?></p>
  </div>
  <div>
    <a class="gs-c-btn" data-type="primary" data-size="regualr" data-style="pill" href="" ><?=$site->newsletterTeaserButtonText()?></a>
  </div>
</div>