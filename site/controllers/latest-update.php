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
        'desc',
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
function latestUpdateToArray($update, bool $for_highlights_link = false): ?array
{
    if (!$update) {
        return null;
    }

    $isNewsletter = $update->intendedTemplate()->name() === 'newsletter';
    $timestamp = latestUpdateTimestamp($update);

    // Bild bestimmen
    if ($isNewsletter) {
        $image_url = url('api/newsletter-cover/' . $update->slug() . '.svg');
    } else {
        $project = $update->parent();
        $coverFile = $project->content()->get('cover')?->toFile();
        $image_url = $coverFile ? $coverFile->url() : null;
    }

    // 🔹 URL bestimmen
    if ($for_highlights_link) {
        $call_to_action_url = url('api/highlights'); // Link auf Highlights
    } else {
        $call_to_action_url = $isNewsletter
            ? $update->url()
            : $update->parent()->url();
    }

    $description = $update->description()->isNotEmpty()
        ? $update->description()->value()
        : $update->text()->excerpt(160)->value();

    $title = $update->title()->value();

    if ($isNewsletter) {
        $description = $title;
        $title = 'Newsletter';
    }

    return [
        'title' => $title,
        'description' => $description,
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
function latestUpdateData(bool $forHighlightsLink = false): ?array
{
    return latestUpdateToArray(latestUpdate(), $forHighlightsLink);
}
