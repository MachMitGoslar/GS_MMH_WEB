<?php

/**
 * Image helpers for custom API routes.
 */

if (!function_exists('mmhApiJpegImageUrl')) {
    function mmhApiJpegImageUrl($file): ?string
    {
        if (!$file) {
            return null;
        }

        try {
            return $file->thumb([
                'width' => 1200,
                'height' => 630,
                'crop' => true,
                'format' => 'jpg',
                'quality' => 88,
            ])->url();
        } catch (Throwable) {
            $extension = strtolower($file->extension());

            return in_array($extension, ['jpg', 'jpeg'], true) ? $file->url() : null;
        }
    }
}

if (!function_exists('mmhApiCoverMeta')) {
    function mmhApiCoverMeta(string $kind, string $slug): ?array
    {
        if ($kind === 'newsletter') {
            $page = page('newsletter/' . $slug);

            return $page ? [
                'kind' => $kind,
                'slug' => $slug,
                'label' => 'Newsletter',
                'title' => $page->title()->value(),
                'colors' => ['#5d4e37', '#6b5b47', '#4a3c28'],
            ] : null;
        }

        if ($kind === 'notes') {
            $page = page('notes/' . $slug);

            return $page ? [
                'kind' => $kind,
                'slug' => $slug,
                'label' => 'Tagebuch',
                'title' => $page->title()->value(),
                'colors' => ['#39556b', '#46677f', '#2d475a'],
            ] : null;
        }

        if ($kind === 'app' && $slug === 'whatsapp-community') {
            return [
                'kind' => $kind,
                'slug' => $slug,
                'label' => 'WhatsApp Community',
                'title' => 'Tritt unserer WhatsApp Community bei.',
                'colors' => ['#245f53', '#1f514d', '#183d4f'],
            ];
        }

        return null;
    }

    function mmhApiCoverFileUrl(string $kind, string $slug): string
    {
        $routes = [
            'newsletter' => 'newsletter-cover',
            'notes' => 'notes-cover',
            'app' => 'app-cover',
        ];

        return url('api/' . ($routes[$kind] ?? $kind . '-cover') . '/' . $slug . '.jpg');
    }

    function mmhApiCoverSvgUrl(string $kind, string $slug): string
    {
        $routes = [
            'newsletter' => 'newsletter-cover',
            'notes' => 'notes-cover',
            'app' => 'app-cover',
        ];

        return url('api/' . ($routes[$kind] ?? $kind . '-cover') . '/' . $slug . '.svg');
    }

    function mmhApiXmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    function mmhApiWrapSvgText(string $text, int $maxLength = 42): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $test = trim($line . ' ' . $word);

            if ($line !== '' && mb_strlen($test) > $maxLength) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $test;
            }
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return array_slice($lines, 0, 2);
    }

    function mmhApiCoverFileSlug(string $kind, string $slug): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]+/', '-', $kind . '-' . $slug);
    }

    function mmhApiCoverJpegPath(string $kind, string $slug): string
    {
        return dirname(__DIR__, 2) . '/public/media/api-covers/' . mmhApiCoverFileSlug($kind, $slug) . '.jpg';
    }

    function mmhApiCoverJpegHashPath(string $kind, string $slug): string
    {
        return dirname(__DIR__, 2) . '/public/media/api-covers/' . mmhApiCoverFileSlug($kind, $slug) . '.sha256';
    }

    function mmhApiCoverLogoDataUri(): ?string
    {
        $logo = dirname(__DIR__, 2) . '/public/assets/svg/RZ-RGB_MM!2_iv.svg';

        if (!is_file($logo)) {
            return null;
        }

        return 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logo));
    }

    function mmhApiCoverSvg(string $kind, string $slug): ?string
    {
        $meta = mmhApiCoverMeta($kind, $slug);
        $logo = mmhApiCoverLogoDataUri();

        if (!$meta || !$logo) {
            return null;
        }

        $label = mmhApiXmlEscape($meta['label']);
        $logo = mmhApiXmlEscape($logo);
        $titleLines = mmhApiWrapSvgText($meta['title']);
        $titleSvg = '';
        $titleY = 500;

        foreach ($titleLines as $line) {
            $titleSvg .= '<text x="600" y="' . $titleY . '" text-anchor="middle" class="title">' . mmhApiXmlEscape($line) . '</text>';
            $titleY += 38;
        }

        $colors = array_map('mmhApiXmlEscape', $meta['colors']);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630" role="img" aria-label="{$label}">
  <defs>
    <linearGradient id="background" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$colors[0]}"/>
      <stop offset="50%" stop-color="{$colors[1]}"/>
      <stop offset="100%" stop-color="{$colors[2]}"/>
    </linearGradient>
    <style>
      text {
        font-family: Arial, Helvetica, sans-serif;
        fill: #fff;
      }

      .label {
        font-size: 60px;
        font-weight: 700;
      }

      .title {
        fill: #f4efe8;
        font-size: 28px;
        font-weight: 700;
      }
    </style>
  </defs>
  <rect width="1200" height="630" fill="url(#background)"/>
  <image href="{$logo}" x="460" y="105" width="280" height="256" preserveAspectRatio="xMidYMid meet"/>
  <text x="600" y="441" text-anchor="middle" class="label">{$label}</text>
  {$titleSvg}
</svg>
SVG;

        return $svg;
    }

    function mmhApiCoverSvgResponse(string $kind, string $slug): Kirby\Cms\Response
    {
        $svg = mmhApiCoverSvg($kind, $slug);

        if (!$svg) {
            return new Kirby\Cms\Response('Not found', 'text/plain', 404);
        }

        return new Kirby\Cms\Response([
            'body' => $svg,
            'type' => 'image/svg+xml',
            'headers' => [
                'Cache-Control' => 'public, max-age=3600',
            ],
            'charset' => 'utf-8',
        ]);
    }

    function mmhApiGenerateCoverJpeg(string $kind, string $slug, string $svg): bool
    {
        if (!class_exists(\Choowx\RasterizeSvg\Svg::class)) {
            return false;
        }

        $target = mmhApiCoverJpegPath($kind, $slug);
        $directory = dirname($target);

        if (!is_dir($directory) && mkdir($directory, 0775, true) === false) {
            return false;
        }

        try {
            $jpeg = \Choowx\RasterizeSvg\Svg::make($svg)->toJpg();
        } catch (Throwable) {
            return false;
        }

        if (!is_string($jpeg) || substr($jpeg, 0, 3) !== "\xff\xd8\xff") {
            return false;
        }

        if (file_put_contents($target, $jpeg, LOCK_EX) === false) {
            return false;
        }

        file_put_contents(mmhApiCoverJpegHashPath($kind, $slug), hash('sha256', $svg), LOCK_EX);

        return true;
    }

    function mmhApiCoverJpegResponse(string $kind, string $slug): Kirby\Cms\Response
    {
        $svg = mmhApiCoverSvg($kind, $slug);

        if (!$svg) {
            return new Kirby\Cms\Response('Not found', 'text/plain', 404);
        }

        $file = mmhApiCoverJpegPath($kind, $slug);
        $hashFile = mmhApiCoverJpegHashPath($kind, $slug);
        $hash = hash('sha256', $svg);
        $cached = is_file($file)
            && is_file($hashFile)
            && trim((string) file_get_contents($hashFile)) === $hash;

        if (!$cached && !mmhApiGenerateCoverJpeg($kind, $slug, $svg)) {
            return new Kirby\Cms\Response(
                'Cover could not be rasterized. Check that node and sharp are installed.',
                'text/plain',
                500,
            );
        }

        $jpeg = file_get_contents($file);

        if (ob_get_level() > 0) {
            ob_clean();
        }

        return new Kirby\Cms\Response([
            'body' => $jpeg,
            'type' => 'image/jpeg',
            'headers' => [
                'Content-Length' => strlen($jpeg),
                'Cache-Control' => 'public, max-age=3600',
            ],
            'charset' => '',
        ]);
    }
}
