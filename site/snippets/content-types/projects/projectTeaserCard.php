<?php

/**
 * Card for one project.
 *
 * @var \Kirby\Cms\Page $project
 */
?>
<?php snippet('utilities/card', [
    'variant' => 'project',
    'image' => $project->cover(),
    'meta' => function () use ($project) {
        ?>
        <?php snippet('utilities/meta-line', slots: true) ?>
          <?php slot('start') ?>
            <?php snippet('content-types/projects/statusBadge', ['status' => $project->effectiveProjectStatus()]) ?>
          <?php endslot() ?>
          <?php slot('end') ?>
            <time class="font-footnote" datetime="<?= $project->modified('c') ?>"><?= $project->modified('d.m.Y') ?></time>
          <?php endslot() ?>
        <?php endsnippet() ?>
        <?php
    },
    'title' => $project->title(),
    'subtitle' => $project->subheadline(),
    'action' => ['label' => 'Zum Projekt', 'href' => $project->url()],
]) ?>
