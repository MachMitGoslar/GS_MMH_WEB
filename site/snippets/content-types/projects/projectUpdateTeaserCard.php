<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/


?>

<li class="c-projectUpdateTeaser-card">
  <div >
    <img class="hero" src="<?=$project->cover()->url()?>">
  </div>
  <div class="content">
    <?= snippet("content-types/projects/statusheader", compact("project_step")) ?>
    <a href="<?= $project_step->parent() ?>">
     <h3 class="font-headline font-line-height-narrow mb-2"><?= $project->title()?></h3>
     <h4 class="font-subheadline font-line-height-narrow mb-2"><?= $project_step->headline()?></h4>
    </a>
    <p class="font-body"><?= $project_step->description()->excerpt(100) ?></p>
  </div>
</li>