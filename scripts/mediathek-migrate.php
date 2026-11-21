<?php

/**
 * Migrates existing images into the Mediathek (see Mmh\Site\MediathekMigration).
 *
 * Plain file operations, Kirby is NOT booted (git-content would commit).
 * Default is a dry run that only writes the report.
 *
 *   ddev exec php scripts/mediathek-migrate.php [content-dir] [--apply] [--report=file.md]
 *
 * Before --apply: content/ clean (`git status --short`), on a branch of its own.
 * After --apply: clear the UUID cache (site/cache/<host>/uuid), check
 * `git diff --name-status --diff-filter=D`, then commit in content/ (no push
 * to production).
 */

require_once __DIR__ . '/../site/plugins/mmh-site/src/MediathekMigration.php';

$args = array_slice($argv, 1);
$apply = in_array('--apply', $args, true);
$report = null;
$root = dirname(__DIR__) . '/content';

foreach ($args as $arg) {
    if (str_starts_with($arg, '--report=')) {
        $report = substr($arg, 9);
    } elseif (!str_starts_with($arg, '--')) {
        $root = $arg;
    }
}

if (!is_dir($root)) {
    fwrite(STDERR, "Content-Ordner nicht gefunden: $root\n");
    exit(1);
}

$migration = new Mmh\Site\MediathekMigration($root);
$plan = $migration->plan();
$markdown = $migration->report($plan);

if ($report !== null) {
    file_put_contents($report, $markdown);
    echo "Bericht: $report\n";
} else {
    echo $markdown, "\n";
}

if (!$apply) {
    echo "\nTrockenlauf – nichts geändert. Mit --apply ausführen.\n";
    exit(0);
}

$migration->apply($plan);
echo "\nAusgeführt. Jetzt UUID-Cache leeren (site/cache/*/uuid) und Ergebnis prüfen.\n";
