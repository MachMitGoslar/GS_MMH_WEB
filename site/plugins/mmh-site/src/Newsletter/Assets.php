<?php

declare(strict_types=1);

namespace Mmh\Site\Newsletter;

/**
 * Inlines local stylesheets, @import chains and files (as data URIs) into the newsletter HTML, and resolves public paths to files on disk (including the media fallback). Moved from the former controllers/newsletter-email.php.
 */
final class Assets
{
    public static function inlineLocalStylesheets(string $html): string
    {
        return preg_replace_callback(
            '/<link\b([^>]*?)\bhref=(["\'])(\/assets\/css\/[^"\']+)\2([^>]*)>/i',
            function (array $matches): string {
                $path = parse_url($matches[3], PHP_URL_PATH);
                $file = self::publicFilePath($path);

                if ($file === null) {
                    return $matches[0];
                }

                $css = file_get_contents($file);

                if ($css === false) {
                    return $matches[0];
                }

                $css = self::inlineCssImports($css, dirname($path));
                $css = self::inlineCssUrls($css, dirname($path));

                return '<style data-inline-source="' . esc($matches[3]) . '">' . "\n" . $css . "\n" . '</style>';
            },
            $html,
        ) ?? $html;
    }

    public static function inlineCssImports(string $css, string $basePath): string
    {
        return preg_replace_callback(
            '/@import\s+url\((["\']?)([^)"\']+)\1\)\s*;/i',
            function (array $matches) use ($basePath): string {
                $url = trim($matches[2]);

                if (preg_match('!^(https?:)?//!i', $url) === 1) {
                    return '';
                }

                $path = self::normalizePublicPath($url, $basePath);
                $file = self::publicFilePath($path);

                if ($file === null) {
                    return $matches[0];
                }

                $imported = file_get_contents($file);

                if ($imported === false) {
                    return $matches[0];
                }

                $imported = self::inlineCssImports($imported, dirname($path));

                return self::inlineCssUrls($imported, dirname($path));
            },
            $css,
        ) ?? $css;
    }

    public static function inlineCssUrls(string $css, string $basePath): string
    {
        return preg_replace_callback(
            '/url\((["\']?)([^)"\']+)\1\)/i',
            function (array $matches) use ($basePath): string {
                $url = trim($matches[2]);

                if (
                    $url === ''
                    || str_starts_with($url, 'data:')
                    || str_starts_with($url, '#')
                    || preg_match('!^(https?:)?//!i', $url) === 1
                ) {
                    return $matches[0];
                }

                $path = self::normalizePublicPath($url, $basePath);
                $dataUri = self::dataUriForPublicPath($path);

                if ($dataUri === null) {
                    return $matches[0];
                }

                return 'url(' . $matches[1] . $dataUri . $matches[1] . ')';
            },
            $css,
        ) ?? $css;
    }

    public static function inlineLocalAssets(string $html): string
    {
        return preg_replace_callback(
            '/\b(src|href)=([\'"])(\/(?:assets|media)\/[^\'"]+)\2/i',
            function (array $matches): string {
                $dataUri = self::dataUriForPublicPath($matches[3]);

                if ($dataUri === null) {
                    return $matches[0];
                }

                return $matches[1] . '=' . $matches[2] . $dataUri . $matches[2];
            },
            $html,
        ) ?? $html;
    }

    public static function normalizePublicPath(string $url, string $basePath): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        $parts = explode('/', trim($basePath . '/' . $path, '/'));
        $normalized = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($normalized);

                continue;
            }

            $normalized[] = $part;
        }

        return '/' . implode('/', $normalized);
    }

    public static function dataUriForPublicPath(string $path): ?string
    {
        if ($path === '/assets/generated/mmh-logo-white.png') {
            return null;
        }

        if (str_starts_with($path, '/media/')) {
            return null;
        }

        $file = self::publicFilePath($path);

        if ($file === null) {
            return null;
        }

        $size = filesize($file);
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if ($size !== false && $size > 750000 && $extension !== 'svg') {
            return null;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            return null;
        }

        $mime = mime_content_type($file) ?: 'application/octet-stream';

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    public static function publicFilePath(?string $path): ?string
    {
        if (!is_string($path) || $path === '' || str_starts_with($path, '//')) {
            return null;
        }

        $path = rawurldecode(parse_url($path, PHP_URL_PATH) ?? '');

        if ($path === '' || !str_starts_with($path, '/')) {
            return null;
        }

        $publicFile = self::fileInRoot(kirby()->root('index'), $path);

        if ($publicFile !== null) {
            return $publicFile;
        }

        if (str_starts_with($path, '/media/')) {
            $mediaFile = self::fileInRoot(kirby()->root('media'), substr($path, 6));

            if ($mediaFile !== null) {
                return $mediaFile;
            }

            $mediaFallback = self::mediaFallbackPath($path);

            if ($mediaFallback !== null) {
                return $mediaFallback;
            }
        }

        return null;
    }

    public static function fileInRoot(string $root, string $path): ?string
    {
        $root = realpath(rtrim($root, DIRECTORY_SEPARATOR));

        if ($root === false) {
            return null;
        }

        $file = realpath($root . '/' . ltrim($path, '/'));

        if ($file === false || !is_file($file)) {
            return null;
        }

        if (!str_starts_with($file, $root . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $file;
    }

    public static function mediaFallbackPath(string $path): ?string
    {
        $parts = explode('/', trim($path, '/'));

        if (count($parts) < 5 || $parts[0] !== 'media' || $parts[1] !== 'pages') {
            return null;
        }

        $filename = array_pop($parts);
        array_shift($parts);
        array_shift($parts);
        array_pop($parts);

        $mediaPattern = rtrim(kirby()->root('media'), DIRECTORY_SEPARATOR)
            . '/pages/'
            . implode('/', array_map('rawurlencode', $parts))
            . '/*/'
            . $filename;
        $mediaMatches = glob($mediaPattern);

        if (is_array($mediaMatches) && isset($mediaMatches[0]) && is_file($mediaMatches[0])) {
            return realpath($mediaMatches[0]) ?: null;
        }

        $contentMatches = self::contentFileMatches($parts, $filename);

        if (isset($contentMatches[0]) && is_file($contentMatches[0])) {
            return realpath($contentMatches[0]) ?: null;
        }

        return null;
    }

    public static function contentFileMatches(array $parts, string $filename, string $base = ''): array
    {
        if ($parts === []) {
            $file = rtrim(kirby()->root('content'), DIRECTORY_SEPARATOR)
                . ($base !== '' ? '/' . $base : '')
                . '/'
                . $filename;

            return is_file($file) ? [$file] : [];
        }

        $part = array_shift($parts);
        $root = rtrim(kirby()->root('content'), DIRECTORY_SEPARATOR)
            . ($base !== '' ? '/' . $base : '');
        $matches = [];

        foreach ([$part, '*_' . $part] as $pattern) {
            foreach (glob($root . '/' . $pattern, GLOB_ONLYDIR) ?: [] as $dir) {
                $relative = ltrim(str_replace(rtrim(kirby()->root('content'), DIRECTORY_SEPARATOR), '', $dir), '/');
                $matches = array_merge($matches, self::contentFileMatches($parts, $filename, $relative));
            }
        }

        return $matches;
    }
}
