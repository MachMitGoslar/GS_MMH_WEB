<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<li class="c-projectTeaserCard">
  <div>
    <img class="c-projectTeaserCard-hero" src=" <?=$project->cover()->url() ?: print("https://picsum.photos/1600/800?random=2") ?> " />
  </div>
  <div class="c-projectTeaserCard-content">
    <div class="c-projectTeaserCard-statusheader">
      <div class="c-projectTeaserCard-badge mb-2" data-color="<?=$project->dataColor()?>"><?= $project->project_status()?></div>
      <time><?=date("d.m.Y")?></time>
    </div>

    <h3 class="font-headline"><?=$project->title()?></h3>
    <p></p>
    <p class="font-footnote mb-3"><?=$project->subheadline()?></p>
    <!-- <p class="font-body"><?=$project->text()->excerpt()?></p> -->
    <a href="<?=$project?>" class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill">Zum Projekt</a>
  </div>
</li>