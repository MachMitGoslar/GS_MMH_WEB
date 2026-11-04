<?php

declare(strict_types=1);

namespace Mmh\Site;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

/**
 * The site's stylesheet bundle: one version-stamped file in media/css that
 * flattens the @import tree of assets/css/index.css, so a deploy busts a
 * single URL. Moved from helpers.php. (The newsletter has its own, different
 * inlining in Newsletter\Assets: it drops remote imports and turns url()
 * into data URIs, while this one hoists them and rebases to root paths.)
 */
final class Stylesheet
{
    /**
     * Cache-busting version for the CSS bundle.
     *
     * `index.css` only pulls in other files via @import, so its own mtime does
     * not change when a component stylesheet is edited — returning visitors keep
     * the stale bundle. Using the newest mtime of the whole tree fixes that.
     */
    public static function version(): int
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
    public static function bundle(): string
    {
        static $path = null;

        if ($path !== null) {
            return $path;
        }

        $version = self::version();
        $entry = kirby()->root('index') . '/assets/css/index.css';
        $fallback = 'assets/css/index.css?version=' . $version;

        if (is_file($entry) === false) {
            return $path = $fallback;
        }

        $dir = kirby()->root('media') . '/css';
        $file = $dir . '/site.' . $version . '.css';

        if (is_file($file) === false) {
            try {
                Dir::make($dir, true);

                foreach (Dir::files($dir) as $stale) {
                    if (preg_match('!^site\.\d+\.css$!', $stale) === 1) {
                        F::remove($dir . '/' . $stale);
                    }
                }

                F::write($file, self::inline($entry));
            } catch (\Throwable $e) {
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
    public static function inline(string $entry): string
    {
        $remote = [];
        $seen = [];
        $body = self::inlinePart($entry, $remote, $seen);
        $head = implode("\n", array_unique($remote));

        return $head === '' ? $body : $head . "\n\n" . $body;
    }

    /**
     * Recursive worker for self::inline().
     *
     * @param array<int,string> $remote  Collected remote @import rules
     * @param array<string,bool> $seen   Guards against importing a file twice
     */
    private static function inlinePart(string $file, array &$remote, array &$seen): string
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

                return self::inlinePart($dir . '/' . $target, $remote, $seen);
            },
            $css,
        );

        return self::rebaseUrls($css, $dir);
    }

    /**
     * Rewrite relative url() references to root-relative paths.
     *
     * Once flattened, every rule lives in /media/css/ instead of the directory it
     * was written in, so a relative url() would point at nothing. Absolute, data:
     * and protocol-relative references are left alone.
     */
    public static function rebaseUrls(string $css, string $dir): string
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
            $css,
        );
    }
}
