<?php

/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/

// Prefer the step's own image, fall back to the parent project's cover
$projectUpdateCover = $project_step->content()->get('image')->toFile() ?? $project->cover();
?>

<li class="c-projectUpdateTeaser-card">
  <div class="hero-wrapper">
    <?php if ($projectUpdateCover) : ?>
      <img class="hero" src="<?= $projectUpdateCover->url() ?>"<?= $projectUpdateCover->focus()->isNotEmpty() ? ' style="object-position: ' . $projectUpdateCover->focus() . '"' : '' ?> alt="<?= $projectUpdateCover->alt() ?>">
    <?php else : ?>
        <?php snippet('utilities/imagePlaceholder') ?>
    <?php endif; ?>
  </div>
  <div class="content">
    <?= snippet('content-types/projects/statusheader', compact('project_step')) ?>
    <a href="<?= $project_step->parent() ?>">
     <h3 class="font-headline font-line-height-narrow mb-2"><?= $project->title()?></h3>
     <h4 class="font-subheadline font-line-height-narrow mb-2"><?= $project_step->headline()?></h4>
    </a>
    <p class="font-body"><?= $project_step->description()->excerpt(100) ?></p>
  </div>
</li>
