<?php

/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/

$projectCover = $project->cover();
?>
<li class="c-projectTeaserCard">
  <div class="hero-wrapper">
    <?php if ($projectCover) : ?>
      <img class="hero" src="<?= $projectCover->url() ?>"<?= $projectCover->focus()->isNotEmpty() ? ' style="object-position: ' . $projectCover->focus() . '"' : '' ?> alt="<?= $projectCover->alt() ?>" />
    <?php else : ?>
        <?php snippet('utilities/imagePlaceholder') ?>
    <?php endif; ?>
  </div>
  <div class="content">
    <div class="statusheader">
    <?= snippet('content-types/projects/statusBadge', ['status' => $project->effectiveProjectStatus()]) ?>
    <time class="font-footnote" datetime="<?= $project->modified('c') ?>"><?= $project->modified('d.m.Y') ?></time>
    </div>

    <h3 class="font-headline font-line-height-narrow"><?=$project->title()?></h3>
    <p class="font-footnote mb-3"><?=$project->subheadline()?></p>
    <!-- <p class="font-body"><?=$project->text()->excerpt()?></p> -->
    <?php $projectTags = $project->tagList(); ?>
    <?php if (!empty($projectTags)) : ?>
      <p class="project-teaser-tags">
        <?php foreach ($projectTags as $tag) : ?>
          <a href="<?= $site->find('projects')?->url() . '?tag=' . urlencode($tag) ?>" class="project-teaser-tag"><?= esc($tag) ?></a>
        <?php endforeach ?>
      </p>
    <?php endif ?>
    <a href="<?=$project?>" class="gs-c-btn" data-type="secondary" data-size="regular" data-style="pill">Zum Projekt</a>
  </div>
</li>
