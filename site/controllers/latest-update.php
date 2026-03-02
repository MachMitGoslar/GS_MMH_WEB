<?php

use Kirby\Cms\Pages;

function latestUpdateAll(): Pages
{
    // Newsletter sammeln
    $newsletters = page('newsletter')
        ?->children()
        ->listed()
        ?? new Pages([]);

    // Projekte sammeln
    $projektSteps = page('projects')
        ?->children()
        ->map(fn ($p) => $p->children()->listed())
        ->flatten()
        ?? new Pages([]);

    // Beide Collections zusammenführen
    return $newsletters->merge($projektSteps);
}

function latestUpdateTimestamp($p): int
{
    // Newsletter: publish_date prüfen
    if ($p->publish_date()->isNotEmpty()) {
        return $p->publish_date()->toTimestamp();
    }


    // Projekte: project_start_date prüfen
    if ($p->project_start_date()->isNotEmpty()) {
        return $p->project_start_date()->toTimestamp();
    }


    // Letzter Fallback: Folder-Nummer
    return -intval($p->num());
}

function latestUpdate()
{
    $all = latestUpdateAll();

    if ($all->isEmpty()) {
        return null;
    }

    // Sortierung nach Timestamp (absteigend)
    return $all
        ->sortBy(fn ($p) => (int) latestUpdateTimestamp($p), 'desc')
        ->first();
}
