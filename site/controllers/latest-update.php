<?php

// Alle Newsletter
$newsletters = page('newsletter')->children()->listed();

// Alle Projektupdate-Steps
$projektSteps = page('1_projects')->children()
    ->map(function ($project) {
        return $project->children()->listed(); // Steps
    })->flatten();

// Alle Updates in einer Collection zusammenführen
$alleUpdates = $newsletters->merge($projektSteps);

// Sortieren nach Datum
$alleUpdates = $alleUpdates->sort(function ($a, $b) {
    // Datum je nach Typ ermitteln
    $dateA = $a->published_at()->or($a->project_start_date())->toDate();
    $dateB = $b->published_at()->or($b->project_start_date())->toDate();

    return $dateB <=> $dateA; // absteigend
});

// Neuestes Update
$neuestesUpdate = $alleUpdates->first();

// Ausgabe prüfen
echo $neuestesUpdate->title() . ' - ' . ($neuestesUpdate->published_at()->isNotEmpty() ? $neuestesUpdate->published_at()->toDate('Y-m-d') : $neuestesUpdate->project_start_date()->toDate('Y-m-d'));
