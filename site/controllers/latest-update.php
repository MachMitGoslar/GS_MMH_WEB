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

/**
 * Ein Update in API-Response-Format umwandeln
 */
function latestUpdateToArray($update): ?array
{
    if (! $update) {
        return null;
    }

    $timestamp = latestUpdateTimestamp($update);

    // Newsletter vs Projekt-Step unterscheiden
    if ($update->intendedTemplate()->name() === 'newsletter') {

        $image_url = url('api/newsletter-cover/' . $update->slug() . '.svg');
        $call_to_action_url = $update->url();

    } else {

        $project = $update->parent();

        // UUID-Cover sauber auflösen
        $coverFile = $project->content()->get('cover')?->toFile();

        $image_url = $coverFile
            ? $coverFile->url()
            : null;

        $call_to_action_url = $project->url();
    }

    return [
        'id' => $update->id(),
        'title' => $update->title()->value(),
        'description' => $update->description()->isNotEmpty()
            ? $update->description()->value()
            : $update->text()->excerpt(160)->value(),
        'image_url' => $image_url,
        'call_to_action_url' => $call_to_action_url,
        'published_at' => date('Y-m-d\TH:i', $timestamp),
        'widget_type' => null,
    ];
}

/**
 * Komfortfunktion:
 * Liefert direkt das neueste Update als Array
 */
function latestUpdateData(): ?array
{
    return latestUpdateToArray(latestUpdate());
}
