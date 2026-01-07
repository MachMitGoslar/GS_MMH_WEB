<?php
/**
* Team Member Card Component
* @var \Kirby\Cms\Page $teamMember
*/
?>

<div class="c-teamMemberCard">
    <a href="<?= $teamMember->url() ?>" >
  <div class="profile-image">
    <?php if ($teamMember->cover() && $teamMember->cover()->toFile()) : ?>
      <img src="<?= $teamMember->cover()->crop(200, 200)->url() ?>" 
           alt="<?= $teamMember->name()->html() ?>" 
           loading="lazy">
    <?php else : ?>
      <div class="placeholder-avatar">
        <span><?= strtoupper(substr($teamMember->name()->value(), 0, 1)) ?></span>
      </div>
    <?php endif ?>
  </div>
  </a>
  <div class="content">
    <a href="<?= $teamMember->url() ?>" > 
    <h3 class="name"><?= $teamMember->name()->html() ?></h3>
    
    <?php if ($teamMember->role()->isNotEmpty()) : ?>
      <div class="role"><?= $teamMember->role()->html() ?></div>
    <?php endif ?>
    
    <?php if ($teamMember->description()->isNotEmpty()) : ?>
      <p class="description"><?= $teamMember->description()->excerpt(100) ?></p>
    <?php endif ?>
    </a>   
    
    <?php if ($teamMember->hasAnyContactInfo()) : ?>
      <div class="contact-links">
        <?php if ($teamMember->email()->isNotEmpty()) : ?>
          <a href="mailto:<?= $teamMember->email()->value() ?>" class="contact-link email" aria-label="E-Mail">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
            </svg>
          </a>
        <?php endif ?>
        
        <?php if ($teamMember->phone()->isNotEmpty()) : ?>
          <a href="tel:<?= $teamMember->phone()->value() ?>" class="contact-link phone" aria-label="Telefon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
            </svg>
        </a>
        <?php endif ?>
      </div>
    <?php endif ?>
  </div>
        </div>