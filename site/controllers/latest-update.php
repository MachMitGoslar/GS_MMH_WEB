<?php

function latestUpdateAll()
{
    $newsletters = page('newsletter')
        ?->children()
        ->listed() ?? pages();

    $projektSteps = page('1_projects')
        ?->children()
        ->map(fn ($p) => $p->children()->listed())
        ->flatten() ?? pages();

    return $newsletters->merge($projektSteps);
}

function latestUpdateTimestamp($p): int
{
    if ($p->publish_date()->isNotEmpty()) {
        return $p->publish_date()->toTimestamp();
    }

    if (method_exists($p, 'published') && $p->published()) {
        return $p->published()->toTimestamp();
    }

    if ($p->project_start_time()->isNotEmpty()) {
        return $p->project_start_time()->toTimestamp();
    }

    if ($p->modified()) {
        return $p->modified();
    }

    return -intval($p->num());
}

function latestUpdate()
{
    $all = latestUpdateAll();

    if ($all->isEmpty()) {
        return null;
    }

    return $all
        ->sortBy(fn ($p) => latestUpdateTimestamp($p), 'desc')
        ->first();
}
