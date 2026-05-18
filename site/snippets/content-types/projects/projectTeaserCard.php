<?php

/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<li class="c-projectTeaserCard">
  <div>
    <img class="hero" src=" <?=$project->cover()->url() ?: print('https://picsum.photos/1600/800?random=2') ?> " />
  </div>
  <div class="content">
    <div class="statusheader">
      <div class="status-badges">
        <?= snippet('content-types/projects/statusBadge', ['status' => $project->project_status()]) ?>
        <?php if ($project->is_external()->toBool()) : ?>
          <div class="status-badge status-badge--external mb-2">extern</div>
        <?php endif; ?>
      </div>
      <time><?=date('d.m.Y')?></time>
    </div>

    <h3 class="font-headline"><?=$project->title()?></h3>
    <p></p>
    <p class="font-footnote mb-3"><?=$project->subheadline()?></p>
    <!-- <p class="font-body"><?=$project->text()->excerpt()?></p> -->
    <a href="<?=$project?>" class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill">Zum Projekt</a>
  </div>
</li>
