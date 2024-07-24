<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<li class="c-element rounded-lg border border-dead-pixel-25 bg-white">
  <div >
    <img class="c-projectUpdateTeaserCard-hero" src="<?=$project_update->cover()->url()?>">
  </div>
  <div class="c-projectUpdateTeaserCard-content">
    <div class="c-projectUpdateTeaserCard-statusheader">
      <?php snippet("components/project/projectStatusBadge", ["project_status" => $project_update->project_status()]) ?>
      <time><?=$project_update->change_date() ?></time>
    </div>
    <h3 class="text-headline font-line-height-narrow mb-2"><?= $project_update->headline() ?></h3>
    <p class="text-body"><?=$project_update->description() ?></p>
  </div>
</li>