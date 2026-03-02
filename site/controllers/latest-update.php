<?php

use Kirby\Cms\Pages;

/**
 * Alle neuesten Updates sammeln (Newsletter + Projekt-Steps)
 */
function latestUpdateAll(): Pages
{
    // Newsletter sammeln
    $newsletters = page('newsletter')
        ?->children()
        ->listed()
        ?? new Pages([]);


    // Projekt-Steps sammeln
    $projektSteps = new Pages([]);
    $projects = page('projects')?->children()->listed() ?? new Pages([]);

    foreach ($projects as $project) {
        $steps = $project->children()->listed() ?? new Pages([]);

        $projektSteps = $projektSteps->merge($steps);
    }

    // Steps nach project_start_date absteigend sortieren
    $projektSteps = $projektSteps->sortBy(
        fn ($step) => $step->project_start_date()->isNotEmpty()
            ? strtotime($step->project_start_date()->value())
            : 0,
        'desc'
    );


    // Newsletter + ProjektSteps zusammenführen
    $all = $newsletters->merge($projektSteps);

    return $all;
}

/**
 * Timestamp für Sortierung bestimmen
 */
function latestUpdateTimestamp($p): int
{
    $title = $p->title()->value();
    $published = $p->published()->value() ?? 'leer';
    $projectStart = $p->project_start_date()->value() ?? 'leer';

    if ($p->published()->isNotEmpty()) {
        $ts = strtotime($p->published()->value());

        return $ts;
    }

    if ($p->project_start_date()->isNotEmpty()) {
        $ts = strtotime($p->project_start_date()->value());

        return $ts;
    }

    $ts = $p->modified()->toTimestamp();

    return $ts;
}

/**
 * Das neueste Update ermitteln
 */
function latestUpdate()
{
    $all = latestUpdateAll();

    if ($all->isEmpty()) {
        return null;
    }

    $sorted = $all->sortBy(fn ($p) => latestUpdateTimestamp($p), 'desc');

    return $sorted->first();
}
