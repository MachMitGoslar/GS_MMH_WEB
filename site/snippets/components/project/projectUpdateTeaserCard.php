<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/


?>

<li class="c-projectUpdateTeaserCard">
  <div >
    <img class="c-projectUpdateTeaserCard-hero" src="<?=$project->cover()->url()?>">
  </div>
  <div class="c-projectUpdateTeaserCard-content">
    <div class="c-projectUpdateTeaserCard-statusheader">
      <div>
      <?php if($project_step->project_status_from()->isNotEmpty() ): ?>
        <div class="c-projectUpdateTeaserCard-badge mb-2" data-color="<?= getColor($project_step->project_status_from()) ?>">
          <?= $project_step->project_status_from() ?>
        </div>
      <?php endif ?>
      <?php if($project_step->project_status_to()->isNotEmpty()): ?>
        <span> > </span>

        <div class="c-projectUpdateTeaserCard-badge mb-2" data-color="<?= getColor($project_step->project_status_to()) ?>">
          <?= $project_step->project_status_to()?>
        </div>
      <?php endif ?>
      </div>
      <time class="font-caption"><?=$project_step->project_start_date()->date("d.m.Y") ?: "test"?></time>
    </div>
    <a href="<?= $project_step->parent() ?>">
     <h3 class="font-headline font-line-height-narrow mb-2"><?= $project->title()?></h3>
     <h4 class="font-subheadline font-line-height-narrow mb-2"><?= $project_step->headline()?></h4>
    </a>
    <p class="font-body"><?= $project_step->description()?></p>
  </div>
</li>