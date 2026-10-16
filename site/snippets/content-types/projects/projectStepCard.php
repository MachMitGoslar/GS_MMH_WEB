<?php

/**
 * Card for one project step: the one layout of a step, on the start page, in
 * the project timeline and in the Panel preview. Built on utilities/card.
 *
 * feed     (start page, newsletter): image of the step or its project, project
 *          title, step headline and excerpt, links to the project
 * timeline (project page): no image, the step's title and full description,
 *          no link (the page is the project itself)
 *
 * The class c-projectUpdateTeaser-card is a hook of the newsletter e-mail
 * styles (controllers/newsletter-email.php), keep it.
 *
 * @var \Kirby\Cms\Page $project_step
 * @var \Kirby\Cms\Page|null $project Defaults to the step's parent
 * @var string|null $context feed (default) or timeline
 * @var string|null $tag Wrapper element (default: li)
 */

$project ??= $project_step->parent();
$context ??= 'feed';
$tag ??= 'li';
$isTimeline = $context === 'timeline';

$url = $project->url();
$startDate = $project_step->project_start_date()->toDate('Y-m-d', null);
$showOutlook = $startDate !== null && $startDate > date('Y-m-d');

$card = [
    'variant' => 'update',
    'class' => 'c-projectUpdateTeaser-card',
    'tag' => $tag,
    'meta' => function () use ($project_step) {
        snippet('content-types/projects/statusheader', compact('project_step'));
    },
];

if ($isTimeline) {
    $card += [
        'placeholder' => false,
        'title' => $project_step->title()->html(),
        'text' => $project_step->description()->kt(),
    ];
} else {
    // Prefer the step's own image, fall back to the project's cover
    $cover = $project_step->content()->get('image')->toFile() ?? $project->cover();
    $card += [
        'image' => $cover,
        'imageHref' => $url,
        'imageLabel' => $project->title()->value(),
        'overlay' => $showOutlook ? function () {
            snippet('utilities/badge', ['label' => 'Ausblick', 'theme' => 'highlight']);
        } : null,
        'title' => $project->title()->html(),
        'titleHref' => $url,
        'body' => function () use ($project_step, $url) {
            ?>
            <h4 class="font-subheadline font-line-height-narrow mb-2"><a href="<?= $url ?>"><?= $project_step->headline() ?></a></h4>
            <p class="font-footnote"><?= $project_step->description()->excerpt(100) ?></p>
            <?php
        },
    ];
}

snippet('utilities/card', $card);
