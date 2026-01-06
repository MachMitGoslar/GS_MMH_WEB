<?php
/**
* Team Gallery Section Component
* @var \Kirby\Cms\Collection $teamMembers
* @var string $title
* @var string $subtitle
*/

if (! isset($teamMembers) || $teamMembers->count() === 0) {
    return;
}
?>

<section class="c-teamGallery">
  <?php if (isset($title)) : ?>
    <h2 class="section-title"><?= $title ?></h2>
  <?php endif ?>
  
  <?php if (isset($subtitle)) : ?>
    <p class="section-subtitle"><?= $subtitle ?></p>
  <?php endif ?>
  
  <div class="team-grid">
    <?php foreach ($teamMembers as $member) : ?>
        <?= snippet('components/team/teamMemberCard', ['teamMember' => $member]) ?>
    <?php endforeach ?>
  </div>
</section>