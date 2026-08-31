<?php

return function ($site, $page, $kirby) {

    $query = get('q');

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

    /**
     * Group the active projects by topic.
     *
     * A project appears under its primary topic and under every secondary one,
     * so cross-cutting projects (e.g. digital participation for seniors) are
     * findable from more than one heading. `?thema=<slug>` narrows to one group.
     */
    $topics = kirby()->option('mmh.topics', []);
    $activeTopic = get('thema');
    if ($activeTopic !== null && !isset($topics[$activeTopic])) {
        $activeTopic = null;
    }

    $groupedProjects = [];
    $topicFilters = [];

    foreach ($topics as $slug => $label) {
        $inTopic = $activeProjects->filter(
            fn ($project) => in_array($slug, $project->topicSlugs(), true),
        )->sortBy(fn ($project) => $project->latestStepDate(), 'desc');

        if ($inTopic->count() === 0) {
            continue;
        }

        $topicFilters[$slug] = [
            'label' => $label,
            'count' => $inTopic->count(),
            'url' => $page->url() . ($activeTopic === $slug ? '' : '?thema=' . $slug),
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
    ];
};
