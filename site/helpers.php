<?php

/**
 * Site Helper Functions
 *
 * This file contains site-specific helper functions that support
 * the MachMit!Haus website functionality.
 */

use Kirby\Cms\App as KirbyApp;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

/**
 * Get the color class name for a project status
 *
 * Maps German project status values to CSS color class names
 * for consistent styling across the site.
 *
 * @param string $status The project status in German
 * @return string The corresponding CSS color class name
 */
function getProjectStatusColor(string $status): string
{
    switch ($status) {
        case 'in Planung':
            return 'planning';
        case 'in Vorbereitung':
            return 'preparing';
        case 'aktiv':
            return 'active';
        case 'in Auswertung':
            return 'review';
        case 'abgeschlossen':
            return 'done';
        default:
            return 'false';
    }
}

/**
 * Return all projects with status badge "abgeschlossen".
 *
 * @param Site $site
 * @return Pages
 */
function getArchivedProjects(Site $site)
{
    return $site->page('projects')
        ?->children()
        ->filter(fn ($project) => $project->effectiveProjectStatus() === 'abgeschlossen');
}

if (!function_exists('mmhTimestampValue')) {
    function mmhTimestampValue($value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_object($value) && method_exists($value, 'toTimestamp')) {
            return (int) $value->toTimestamp();
        }

        return time();
    }
}

/**
 * Returns whether timed content should be visible in the current request.
 *
 * Editors/admins and explicit preview requests can always see timed content.
 *
 * @param object $content Page, layout or block object with optional publish/end date fields
 * @return bool
 */
function isTimedContentVisible(object $content): bool
{
    $kirby = kirby();

    if (($kirby instanceof KirbyApp) === false) {
        return true;
    }

    $user = $kirby->user();

    if ($kirby->request()->get('preview') !== null) {
        return true;
    }

    if (
        $user !== null &&
        in_array($user->role()->name(), ['admin', 'editor'], true)
    ) {
        return true;
    }

    $timezone = new DateTimeZone($kirby->option('date.timezone', 'Europe/Berlin'));
    $now = (new DateTimeImmutable('now', $timezone))->getTimestamp();

    $publish = null;
    if (method_exists($content, 'publish_date') && $content->publish_date()->isNotEmpty()) {
        $publishValue = $content->publish_date()->toDate('Y-m-d H:i');
        $publishDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', $publishValue, $timezone);
        $publish = $publishDate ? $publishDate->getTimestamp() : null;
    }

    $end = null;
    if (method_exists($content, 'end_date') && $content->end_date()->isNotEmpty()) {
        $endValue = $content->end_date()->toDate('Y-m-d H:i');
        $endDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', $endValue, $timezone);
        $end = $endDate ? $endDate->getTimestamp() : null;
    }

    if (($publish && $publish > $now) || ($end && $end < $now)) {
        return false;
    }

    return true;
}

/**
 * Cache-busting version for the CSS bundle.
 *
 * `index.css` only pulls in other files via @import, so its own mtime does
 * not change when a component stylesheet is edited — returning visitors keep
 * the stale bundle. Using the newest mtime of the whole tree fixes that.
 */
function mmhStylesheetVersion(): int
{
    static $version = null;

    if ($version !== null) {
        return $version;
    }

    $root = kirby()->root('index') . '/assets/css';
    $version = 0;

    foreach (Dir::index($root, true) as $entry) {
        $path = $root . '/' . $entry;

        if (is_file($path) === true) {
            $version = max($version, (int) filemtime($path));
        }
    }

    return $version;
}
/**
 * Path to the bundled stylesheet, ready to hand to `css()`.
 *
 * The component stylesheets reach the browser through the @import tree of
 * `index.css`, so they have URLs of their own that carry no version. Together
 * with `ExpiresByType text/css "access plus 1 year"` in public/.htaccess that
 * means a deploy busts only `index.css` while every imported file keeps being
 * served from cache — visitors end up with a mix of old and new rules. Writing
 * the whole tree into one version-stamped file puts it all behind a single URL.
 *
 * Falls back to the unbundled entry point when the target cannot be written,
 * so a read-only deploy degrades instead of breaking.
 */
function mmhStylesheetBundle(): string
{
    static $path = null;

    if ($path !== null) {
        return $path;
    }

    $version  = mmhStylesheetVersion();
    $entry    = kirby()->root('index') . '/assets/css/index.css';
    $fallback = 'assets/css/index.css?version=' . $version;

    if (is_file($entry) === false) {
        return $path = $fallback;
    }

    $dir  = kirby()->root('media') . '/css';
    $file = $dir . '/site.' . $version . '.css';

    if (is_file($file) === false) {
        try {
            Dir::make($dir, true);

            foreach (Dir::files($dir) as $stale) {
                if (preg_match('!^site\.\d+\.css$!', $stale) === 1) {
                    F::remove($dir . '/' . $stale);
                }
            }

            F::write($file, mmhInlineStylesheet($entry));
        } catch (Throwable $e) {
            return $path = $fallback;
        }
    }

    return $path = 'media/css/site.' . $version . '.css';
}

/**
 * Resolve the @import tree of a stylesheet into a single string.
 *
 * Remote imports (the web fonts) are hoisted to the top: @import is only valid
 * before any other rule, and after flattening they would otherwise sit in the
 * middle of the bundle and be dropped by the browser.
 */
function mmhInlineStylesheet(string $entry): string
{
    $remote = [];
    $seen   = [];
    $body   = mmhInlineStylesheetPart($entry, $remote, $seen);
    $head   = implode("\n", array_unique($remote));

    return $head === '' ? $body : $head . "\n\n" . $body;
}

/**
 * Recursive worker for mmhInlineStylesheet().
 *
 * @param array<int,string> $remote  Collected remote @import rules
 * @param array<string,bool> $seen   Guards against importing a file twice
 */
function mmhInlineStylesheetPart(string $file, array &$remote, array &$seen): string
{
    $real = realpath($file);

    if ($real === false || isset($seen[$real]) === true) {
        return '';
    }

    $seen[$real] = true;

    $css = (string) F::read($real);
    $dir = dirname($real);

    $css = (string) preg_replace_callback(
        '!@import\s+url\(\s*[\'"]?([^\'")]+)[\'"]?\s*\)\s*;!i',
        function (array $match) use ($dir, &$remote, &$seen): string {
            $target = trim($match[1]);

            if (preg_match('!^(?:[a-z][a-z0-9+.-]*:)?//!i', $target) === 1) {
                $remote[] = $match[0];

                return '';
            }

            return mmhInlineStylesheetPart($dir . '/' . $target, $remote, $seen);
        },
        $css
    );

    return mmhRebaseStylesheetUrls($css, $dir);
}

/**
 * Rewrite relative url() references to root-relative paths.
 *
 * Once flattened, every rule lives in /media/css/ instead of the directory it
 * was written in, so a relative url() would point at nothing. Absolute, data:
 * and protocol-relative references are left alone.
 */
function mmhRebaseStylesheetUrls(string $css, string $dir): string
{
    $index = realpath(kirby()->root('index'));

    if ($index === false || str_starts_with($dir, $index) === false) {
        return $css;
    }

    $base = rtrim(str_replace('\\', '/', substr($dir, strlen($index))), '/');

    return (string) preg_replace_callback(
        '~\burl\(\s*([\'"]?)(?!data:|\#|/)([^\'")]+)\1\s*\)~i',
        function (array $match) use ($base): string {
            $target = trim($match[2]);

            if ($target === '' || preg_match('!^[a-z][a-z0-9+.-]*:!i', $target) === 1) {
                return $match[0];
            }

            $parts = [];

            foreach (explode('/', $base . '/' . $target) as $segment) {
                if ($segment === '' || $segment === '.') {
                    continue;
                }

                if ($segment === '..') {
                    array_pop($parts);

                    continue;
                }

                $parts[] = $segment;
            }

            return 'url("/' . implode('/', $parts) . '")';
        },
        $css
    );
}
