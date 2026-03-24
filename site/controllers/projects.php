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
        ];
    }

    /**
     * Load all projects
     */
    $listedProjects = $projectsRoot->children()->listed();

    $activeProjects = $listedProjects->filter(
        fn ($project) => $project->project_status()->value() !== 'abgeschlossen'
    );

    $archivedFromProjects = $listedProjects->filter(
        fn ($project) => $project->project_status()->value() === 'abgeschlossen'
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
    ];
};
