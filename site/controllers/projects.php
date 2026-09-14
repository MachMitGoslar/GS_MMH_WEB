<?php

return function ($site, $page, $kirby) {

    $query = get('q');
    $tagQuery = trim((string) get('tag'));

    // project source
    $projectsRoot = $site->find('projects');
    $archiveRoot = $site->find('project-archive');

    if (!$projectsRoot) {
        return [
            'activeProjects' => collect(),
            'archiveProjects' => collect(),
            'archivePage' => null,
            'query' => $query,
            'groupedProjects' => [],
            'topicFilters' => [],
            'activeTopic' => null,
            'tagQuery' => $tagQuery,
            'tagFilters' => [],
            'topicResetUrl' => $page->url(),
        ];
    }

    /**
     * Load all projects
     */
    $listedProjects = $projectsRoot->children()->listed();

    $activeProjects = $listedProjects->filter(
        fn ($project) => $project->effectiveProjectStatus() !== 'abgeschlossen',
    );

    $archivedFromProjects = $listedProjects->filter(
        fn ($project) => $project->effectiveProjectStatus() === 'abgeschlossen',
    );

    $archiveProjects = $archiveRoot
        ? $archiveRoot->children()->listed()->merge($archivedFromProjects)
        : $archivedFromProjects;

    /**
     * Show searchbar if activated
     */
    if ($page->show_search()->toBool() && $query) {
        $archiveProjects = $archiveProjects->filter(function ($project) use ($query) {
            return stripos($project->title()->value(), $query) !== false
                || stripos($project->text()->value(), $query) !== false;
        });
    }

    $topics = kirby()->option('mmh.topics', []);
    $activeTopic = get('thema');
    if ($activeTopic !== null && !isset($topics[$activeTopic])) {
        $activeTopic = null;
    }

    /**
     * Builds a listing URL that carries the given topic/tag filter
     * combination. Used for the topic pills and the tag pills, so clicking
     * one filter never silently drops the other.
     */
    $buildFilterUrl = function (?string $topicSlug, ?string $tag) use ($page) {
        $params = array_filter(
            ['thema' => $topicSlug, 'tag' => $tag],
            fn ($value) => $value !== null && $value !== '',
        );

        return $params === [] ? $page->url() : $page->url() . '?' . http_build_query($params);
    };

    /**
     * Tag pills, shown alongside the topic pills (smaller, secondary). Counts
     * and the pool of tags on offer reflect the current topic selection, not
     * the tag filter itself, so picking a topic first narrows which tags
     * make sense to offer next.
     */
    $topicScopedProjects = $activeTopic === null
        ? $activeProjects
        : $activeProjects->filter(fn ($project) => $project->topicSlug() === $activeTopic);

    $tagFilters = [];

    foreach ($kirby->collection('project-tags') as $tag) {
        $count = $topicScopedProjects->filter(
            fn ($project) => in_array($tag, $project->tagList(), true),
        )->count();

        if ($count === 0) {
            continue;
        }

        $tagFilters[$tag] = [
            'label' => $tag,
            'count' => $count,
            'url' => $buildFilterUrl($activeTopic, $tagQuery === $tag ? null : $tag),
            'isActive' => $tagQuery === $tag,
        ];
    }

    /**
     * Narrow the active listing to projects carrying the selected tag.
     * Composes with the topic filter: both narrow the same set.
     */
    if ($tagQuery !== '') {
        $activeProjects = $activeProjects->filter(
            fn ($project) => in_array($tagQuery, $project->tagList(), true),
        );
    }

    /**
     * Group the active projects by topic.
     *
     * Each project has exactly one topic (`?thema=<slug>` narrows to that
     * group). `?tag=<tag>` narrows further, across all groups, to projects
     * carrying that tag from the shared tag list.
     */
    $groupedProjects = [];
    $topicFilters = [];

    foreach ($topics as $slug => $label) {
        $inTopic = $activeProjects->filter(
            fn ($project) => $project->topicSlug() === $slug,
        )->sortBy(fn ($project) => $project->latestStepDate(), 'desc');

        if ($inTopic->count() === 0) {
            continue;
        }

        $topicFilters[$slug] = [
            'label' => $label,
            'count' => $inTopic->count(),
            'url' => $buildFilterUrl($activeTopic === $slug ? null : $slug, $tagQuery),
            'isActive' => $activeTopic === $slug,
        ];

        if ($activeTopic === null || $activeTopic === $slug) {
            $groupedProjects[$slug] = ['label' => $label, 'projects' => $inTopic];
        }
    }

    /**
     * Show archive page teaser card
     */
    $archivePage = $site->find('project-archive');

    /**
     * Return variables to template
     */
    return [
        'activeProjects' => $activeProjects,
        'archiveProjects' => $archiveProjects,
        'archivePage' => $archivePage,
        'query' => $query,
        'groupedProjects' => $groupedProjects,
        'topicFilters' => $topicFilters,
        'activeTopic' => $activeTopic,
        'tagQuery' => $tagQuery,
        'tagFilters' => $tagFilters,
        'topicResetUrl' => $buildFilterUrl(null, $tagQuery),
    ];

};
