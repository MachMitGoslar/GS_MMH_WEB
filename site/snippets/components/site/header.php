<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<header class="header flex" id="mainNavHeader">
  <div class="mobileMenuWrapper">
    <a class="logo font-body font-weight-semiBold block" href="<?=$site->url()?>">
      <?=svg("assets/svg/machmit-logo.svg")?>
    </a>
    <button id="menu-toggle" aria-expanded="false" aria-controls="menu" class="hamburger"><?=svg("assets/svg/hamburger.svg")?></button>
  </div>
  <nav class="mainNav" id="mainNav">
    <ul class="mainNav-list">
      <?php foreach($site->children()->listed() as $child): ?>
        <li class="mainNav-list-item font-body">
          <a <?php e($child->isOpen(), ' class="active"') ?> href="<?=$child->url()?>"><?=$child->title()?></a>
        </li>
      <?php endforeach ?>
    </ul>
  </nav>
  <div class="placeholder">

  </div>
</header>