<?php

return function ($site, $page, $kirby) {

    $query = get('q');

    // project source
    $projectsRoot = $site->find('projects');

    if (! $projectsRoot) {
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
    $allProjects = $projectsRoot->children()->listed();

    /**
     * get active projects
     */
    $activeProjects = $allProjects->filter(function ($project) {
        return $project->project_status()->value() !== 'abgeschlossen';
    });

    /**
     * get archived projects
     */
    $archiveProjects = $allProjects->filterBy(
        'project_status',
        'abgeschlossen'
    );

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
