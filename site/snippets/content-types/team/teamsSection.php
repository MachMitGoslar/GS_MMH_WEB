<?php

/**  Teams Section Snippet */

/* @var Kirby\Cms\Collection $staff
 * @var Kirby\Cms\Collection $volunteers
 * @var Kirby\Cms\Collection $partners
 * @var Kirby\Cms\Collection $issuers
 * @var string|null $title
 * @var string|null $subtitle
 */
?>


<section class="team-section">
    <div class="grid content">
        <br />
      <div class="grid-item" data-span="1/1">
        
        <!-- Main Team -->
        <?= snippet('content-types/team/teamGallery', [
          'teamMembers' => $staff,
          'title' => 'Hauptamtliches Team',
          'subtitle' => 'Unsere hauptamtlichen Mitarbeiterinnen und Mitarbeiter',
        ]) ?>
        
        <!-- Volunteers -->
        <?= snippet('content-types/team/teamGallery', [
          'teamMembers' => $volunteers,
          'title' => 'Ehrenamtliches Team',
          'subtitle' => 'Engagierte Menschen, die das MachMit!Haus ehrenamtlich unterstützen',
        ]) ?>
        
        <!-- Partners -->
        <?= snippet('content-types/team/teamGallery', [
          'teamMembers' => $partners,
          'title' => 'Partner',
          'subtitle' => 'Unsere wertvollen Partner und Kooperationspartner',
        ]) ?>
        
        <!-- Issuers -->
        <?= snippet('content-types/team/teamGallery', [
          'teamMembers' => $issuers,
          'title' => 'Auftraggeber',
          'subtitle' => 'Institutionen und Organisationen, die uns beauftragen',
        ]) ?>
        
      </div>
    </div>
  </section>