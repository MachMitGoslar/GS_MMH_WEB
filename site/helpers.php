<?php

/**
 * Site Helper Functions
 *
 * This file contains site-specific helper functions that support
 * the MachMit!Haus website functionality.
 */

use Kirby\Cms\Site;

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

if (!function_exists('mmhAvatarImage')) {
    /**
     * Resolve an avatar-style image to a URL and the object-fit mode it
     * needs on the front end.
     *
     * Portrait/square images (typical headshots) are cropped to fill the
     * circle, anchored to the top so faces survive the crop. Wide images
     * (logos, banners, panoramic/group shots) are resized to fit inside
     * the circle instead, since cropping them would cut off content.
     *
     * @param \Kirby\Cms\File $image
     * @param int $size Target square box size in pixels
     * @return array{url: string, fit: string}
     */
    function mmhAvatarImage($image, int $size): array
    {
        $width = $image->width();
        $height = $image->height();
        $isPortraitOrSquare = $height <= 0 || $width <= 0 || $width <= $height * 1.2;

        if ($isPortraitOrSquare) {
            return [
                'url' => $image->crop($size, $size)->url(),
                'fit' => 'cover',
            ];
        }

        return [
            'url' => $image->resize($size, $size)->url(),
            'fit' => 'contain',
        ];
    }
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

if (!function_exists('mmhBackLink')) {
    /**
     * Ziel und Label der einheitlichen Zurück-Navigation (Header-Button + Inline-Link).
     *
     * Standard: Elternseite der aktuellen Seite. Templates können über $override
     * `url` und/or `label` setzen oder mit `false` die Navigation abschalten.
     *
     * @param array{url?: string, label?: string}|false|null $override
     * @return array{url: string, label: string}|null
     */
    function mmhBackLink(?\Kirby\Cms\Page $page, array|false|null $override = null): ?array
    {
        if ($override === false || $page === null || $page->isHomePage()) {
            return null;
        }

        $parent = $page->parent();
        $override = $override ?? [];

        if (empty($override['url']) && ($parent === null || $parent->isDraft())) {
            return null;
        }

        return [
            'url' => $override['url'] ?? $parent->url(),
            'label' => $override['label'] ?? 'Zurück zur Übersicht',
        ];
    }
}
if (!function_exists('mmhMapboxToken')) {
    /**
     * Mapbox access token (public "pk." token, URL-restricted in the Mapbox dashboard).
     *
     * Source of truth is the `mmh.mapbox.token` config option; the Panel field
     * `mapbox_token` on the contact page is only used as fallback.
     */
    function mmhMapboxToken(): string
    {
        $token = trim((string) option('mmh.mapbox.token', ''));

        if ($token === '') {
            $token = trim((string) site()->find('contact')?->mapbox_token()->value());
        }

        return $token;
    }
}

if (!function_exists('mmhVersion')) {
    /**
     * Version of the running website (SemVer, e.g. "1.2.0").
     *
     * Order: environment variable MMH_VERSION (the deployment sets it, e.g.
     * "1.2.0-rc.3" on staging), the file VERSION in the project root (written
     * at deploy time), the version in package.json (kept current by the release
     * workflow), else "unknown".
     */
    function mmhVersion(): string
    {
        $env = trim((string) getenv('MMH_VERSION'));

        if ($env !== '') {
            return $env;
        }

        $root = dirname(__DIR__);
        $file = $root . '/VERSION';

        if (is_file($file) && ($value = trim((string) file_get_contents($file))) !== '') {
            return $value;
        }

        $package = is_file($root . '/package.json') ? json_decode((string) file_get_contents($root . '/package.json'), true) : null;

        return is_array($package) && !empty($package['version']) ? (string) $package['version'] : 'unknown';
    }
}

if (!function_exists('mmhOnce')) {
    /**
     * True the first time a key is asked within one Kirby instance (one request),
     * false afterwards. For things a snippet may be rendered several times for
     * but must only output once, e.g. a script tag.
     */
    function mmhOnce(string $key): bool
    {
        static $seen = null;
        $seen ??= new WeakMap();
        $app = kirby();
        $keys = $seen[$app] ?? [];

        if (isset($keys[$key])) {
            return false;
        }

        $keys[$key] = true;
        $seen[$app] = $keys;

        return true;
    }
}

if (!function_exists('mmhColorContrast')) {
    /**
     * Derives readable colors from a free-form hex color (WCAG 2.x contrast).
     *
     * Returns null for anything that is not a #rrggbb value, so the result is
     * always safe to print into CSS.
     *
     * - bg:  the color itself (surfaces)
     * - on:  text color on top of `bg` (white or dark, whichever contrasts more)
     * - ink: `bg` darkened until it reaches 3:1 against white (text/borders on white)
     */
    function mmhColorContrast(?string $hex): ?array
    {
        $hex = strtolower(trim((string) $hex));
        if (!preg_match('/^#[0-9a-f]{6}$/', $hex)) {
            return null;
        }

        $toRgb = fn (string $h): array => [
            hexdec(substr($h, 1, 2)),
            hexdec(substr($h, 3, 2)),
            hexdec(substr($h, 5, 2)),
        ];
        $toHex = fn (array $c): string => sprintf('#%02x%02x%02x', ...array_map(fn ($v) => (int) round($v), $c));
        $luminance = function (array $c): float {
            [$r, $g, $b] = array_map(function ($v) {
                $v /= 255;

                return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
            }, $c);

            return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        };
        $contrast = function (float $a, float $b): float {
            [$hi, $lo] = $a >= $b ? [$a, $b] : [$b, $a];

            return ($hi + 0.05) / ($lo + 0.05);
        };

        $rgb = $toRgb($hex);
        $lum = $luminance($rgb);
        $dark = '#6e6e6e'; // --color-dead-pixel-1300

        $on = $contrast($lum, 1.0) >= $contrast($lum, $luminance($toRgb($dark))) ? '#ffffff' : $dark;

        $ink = $rgb;
        for ($i = 0; $i < 20 && $contrast($luminance($ink), 1.0) < 3.0; $i++) {
            $ink = array_map(fn ($v) => $v * 0.9, $ink);
        }

        return ['bg' => $hex, 'on' => $on, 'ink' => $toHex($ink)];
    }
}

if (!function_exists('mmhHoroscopeSortSigns')) {
    /**
     * Sorts the signs of the horoscope API by their `order` field
     * (aries → pisces). Since the API started to deliver the five attributes
     * in `order`, an array there counts as 0 and keeps the API's own order.
     *
     * @param array<int, array> $signs
     * @return array<int, array>
     */
    function mmhHoroscopeSortSigns(array $signs): array
    {
        $key = static fn (array $sign): int => is_numeric($sign['order'] ?? null) ? (int) $sign['order'] : 0;
        usort($signs, static fn (array $a, array $b): int => $key($a) <=> $key($b));

        return $signs;
    }
}

if (!function_exists('mmhHoroscopeAttributes')) {
    /**
     * The attribute ratings of one sign, in the order of `$labels`, clamped to
     * 0..$max. Read from `attributes`, or from `order` while the API still
     * delivers them there. Attributes the API did not send are left out.
     *
     * @param array<string, string> $labels API field name => label
     * @return array<string, array{label: string, value: int}>
     */
    function mmhHoroscopeAttributes(array $sign, array $labels, int $max = 8): array
    {
        $source = $sign['attributes'] ?? (is_array($sign['order'] ?? null) ? $sign['order'] : []);
        $attributes = [];

        foreach ($labels as $key => $label) {
            if (!isset($source[$key])) {
                continue;
            }

            $attributes[$key] = ['label' => $label, 'value' => max(0, min($max, (int) $source[$key]))];
        }

        return $attributes;
    }
}

/*
 * Global entry points used by gs-mmh-web-plugin (newsletter mail, recipients).
 * They only delegate; remove them together with the plugin's dependency on the
 * site (docs/CLEANUP.md, phase 4: "Web-Plugin entkoppeln").
 */
if (!function_exists('mmhAbsoluteUrl')) {
    function mmhAbsoluteUrl(string $url): string
    {
        return Mmh\Site\Url::absolute($url);
    }
}

if (!function_exists('mmhNewsletterMobileHtml')) {
    function mmhNewsletterMobileHtml(Kirby\Cms\Page $page, array $data = []): string
    {
        return Mmh\Site\Newsletter\EmailHtml::mobileHtml($page, $data);
    }
}
