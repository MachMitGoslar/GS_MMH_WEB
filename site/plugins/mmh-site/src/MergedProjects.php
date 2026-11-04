<?php

declare(strict_types=1);

namespace Mmh\Site;

/**
 * Projects that were merged into another project as true duplicates.
 * Old slug => new path including the chapter anchor.
 *
 * Related but independent projects are deliberately NOT listed: they keep
 * their own pages and are grouped through their topic.
 */
final class MergedProjects
{
    public const TARGETS = [
        'machmitwald-christmas-challenge' => 'projects/machmitwald#kapitel-machmitwald-christmas-challenge',
        'mein-buntes-goslar' => 'projects/burgerkunst-mein-buntes-goslar#kapitel-mein-buntes-goslar',
        'so-ist-tet' => 'projects/interkulturelle-begegnungen#kapitel-so-ist-tet',
    ];

    /**
     * The path a merged project now lives at, or null for any other slug.
     */
    public static function target(string $slug): ?string
    {
        return self::TARGETS[$slug] ?? null;
    }
}
