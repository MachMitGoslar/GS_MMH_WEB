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

    function mmhApiCoverLogoSvg(): ?string
    {
        $logo = dirname(__DIR__, 2) . '/public/assets/svg/RZ-RGB_MM!2_iv.svg';

        if (!is_file($logo)) {
            return null;
        }

        $document = new DOMDocument();

        if (!$document->load($logo)) {
            return null;
        }

        $logoSvg = $document->documentElement;

        if (!$logoSvg || strtolower($logoSvg->tagName) !== 'svg') {
            return null;
        }

        $viewBox = preg_split('/[\s,]+/', trim($logoSvg->getAttribute('viewBox'))) ?: [];

        if (count($viewBox) !== 4) {
            return null;
        }

        [, , $viewBoxWidth, $viewBoxHeight] = array_map('floatval', $viewBox);

        if ($viewBoxWidth <= 0 || $viewBoxHeight <= 0) {
            return null;
        }

        $scale = min(280 / $viewBoxWidth, 256 / $viewBoxHeight);
        $x = 460 + ((280 - ($viewBoxWidth * $scale)) / 2);
        $y = 105 + ((256 - ($viewBoxHeight * $scale)) / 2);

        foreach ($document->getElementsByTagName('*') as $element) {
            if (!$element instanceof DOMElement) {
                continue;
            }

            $class = $element->getAttribute('class');

            if ($class === 'st0') {
                $element->setAttribute('fill', '#ffffff');
            } elseif ($class === 'st1') {
                $element->setAttribute('fill', '#b09c40');
            }

            $element->removeAttribute('class');
        }

        $logoContent = '';

        foreach ($logoSvg->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'defs') {
                continue;
            }

            $logoContent .= $document->saveXML($child);
        }

        return sprintf(
            '<g transform="translate(%s %s) scale(%s)">%s</g>',
            rtrim(rtrim(sprintf('%.4F', $x), '0'), '.'),
            rtrim(rtrim(sprintf('%.4F', $y), '0'), '.'),
            rtrim(rtrim(sprintf('%.6F', $scale), '0'), '.'),
            $logoContent,
        );
    }

    function mmhApiCoverJpegHash(string $svg): string
    {
        return hash('sha256', $svg . '|imagick-composited-gradient-v1');
    }

    function mmhApiCoverSvg(string $kind, string $slug, bool $includeBackground = true): ?string
    {
        $meta = mmhApiCoverMeta($kind, $slug);
        $logo = mmhApiCoverLogoSvg();

        if (!$meta || !$logo) {
            return null;
        }

        $label = mmhApiXmlEscape($meta['label']);
        $titleLines = mmhApiWrapSvgText($meta['title']);
        $titleSvg = '';
        $titleY = 500;

        foreach ($titleLines as $line) {
            $titleSvg .= '<text x="600" y="' . $titleY . '" text-anchor="middle"'
                . ' font-family="Arial, Helvetica, sans-serif" font-size="28" font-weight="700"'
                . ' fill="#f4efe8">'
                . mmhApiXmlEscape($line)
                . '</text>';
            $titleY += 38;
        }

        $colors = array_map('mmhApiXmlEscape', $meta['colors']);
        $background = $includeBackground ? <<<SVG
  <defs>
    <linearGradient id="background" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$colors[0]}"/>
      <stop offset="50%" stop-color="{$colors[1]}"/>
      <stop offset="100%" stop-color="{$colors[2]}"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="630" fill="url(#background)"/>
SVG : '';

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630"
  role="img" aria-label="{$label}">
  {$background}
  {$logo}
  <text x="600" y="441" text-anchor="middle" font-family="Arial, Helvetica, sans-serif"
    font-size="60" font-weight="700" fill="#ffffff">{$label}</text>
  {$titleSvg}
</svg>
SVG;

        return $svg;
    }

    function mmhApiHexToRgb(string $hex): ?array
    {
        $hex = ltrim($hex, '#');

        if (!preg_match('/^[a-fA-F0-9]{6}$/', $hex)) {
            return null;
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    function mmhApiMixRgb(array $from, array $to, float $amount): array
    {
        $amount = max(0, min(1, $amount));

        return [
            (int) round($from[0] + (($to[0] - $from[0]) * $amount)),
            (int) round($from[1] + (($to[1] - $from[1]) * $amount)),
            (int) round($from[2] + (($to[2] - $from[2]) * $amount)),
        ];
    }

    function mmhApiRgbColor(array $rgb): string
    {
        return sprintf('rgb(%d,%d,%d)', $rgb[0], $rgb[1], $rgb[2]);
    }

    function mmhApiCoverGradientImage(array $colors): ?Imagick
    {
        $start = mmhApiHexToRgb((string) ($colors[0] ?? ''));
        $middle = mmhApiHexToRgb((string) ($colors[1] ?? ''));
        $end = mmhApiHexToRgb((string) ($colors[2] ?? ''));

        if (!$start || !$middle || !$end) {
            return null;
        }

        $image = new Imagick();
        $image->newImage(1200, 630, new ImagickPixel('#000000'), 'png');
        $image->setImageColorspace(Imagick::COLORSPACE_SRGB);

        $iterator = $image->getPixelIterator();

        foreach ($iterator as $y => $pixels) {
            foreach ($pixels as $x => $pixel) {
                $offset = (($x / 1199) + ($y / 629)) / 2;
                $rgb = $offset <= 0.5
                    ? mmhApiMixRgb($start, $middle, $offset * 2)
                    : mmhApiMixRgb($middle, $end, ($offset - 0.5) * 2);

                $pixel->setColor(mmhApiRgbColor($rgb));
            }

            $iterator->syncIterator();
        }

        return $image;
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

    function mmhApiSvgToJpeg(string $svg, array $colors): ?string
    {
        if (!class_exists('Imagick')) {
            return null;
        }

        try {
            $image = mmhApiCoverGradientImage($colors);

            if (!$image) {
                return null;
            }

            $overlay = new Imagick();
            $overlay->setResolution(144, 144);
            $overlay->setBackgroundColor(new ImagickPixel('transparent'));
            $overlay->readImageBlob($svg);
            $overlay->setImageBackgroundColor(new ImagickPixel('transparent'));
            $overlay = $overlay->mergeImageLayers(Imagick::LAYERMETHOD_MERGE);

            if ($overlay->getImageWidth() !== 1200 || $overlay->getImageHeight() !== 630) {
                $overlay->resizeImage(1200, 630, Imagick::FILTER_LANCZOS, 1, true);
                $overlay->extentImage(1200, 630, 0, 0);
            }

            $image->compositeImage($overlay, Imagick::COMPOSITE_OVER, 0, 0);
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality(88);

            return $image->getImagesBlob();
        } catch (Throwable) {
            return null;
        } finally {
            if (isset($overlay) && $overlay instanceof Imagick) {
                $overlay->clear();
                $overlay->destroy();
            }

            if (isset($image) && $image instanceof Imagick) {
                $image->clear();
                $image->destroy();
            }
        }
    }

    function mmhApiGenerateCoverJpeg(string $kind, string $slug, string $svg): bool
    {
        $meta = mmhApiCoverMeta($kind, $slug);
        $foregroundSvg = mmhApiCoverSvg($kind, $slug, false);

        if (!$meta || !$foregroundSvg) {
            return false;
        }

        $target = mmhApiCoverJpegPath($kind, $slug);
        $directory = dirname($target);

        if (!is_dir($directory) && mkdir($directory, 0775, true) === false) {
            return false;
        }

        $temporaryTarget = tempnam($directory, 'cover-');

        if ($temporaryTarget === false) {
            return false;
        }

        $jpeg = mmhApiSvgToJpeg($foregroundSvg, $meta['colors']);

        if (!is_string($jpeg) || substr($jpeg, 0, 3) !== "\xff\xd8\xff") {
            @unlink($temporaryTarget);

            return false;
        }

        if (file_put_contents($temporaryTarget, $jpeg, LOCK_EX) === false) {
            @unlink($temporaryTarget);

            return false;
        }

        if (!rename($temporaryTarget, $target)) {
            @unlink($temporaryTarget);

            return false;
        }

        file_put_contents(mmhApiCoverJpegHashPath($kind, $slug), mmhApiCoverJpegHash($svg), LOCK_EX);

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
        $hash = mmhApiCoverJpegHash($svg);
        $cached = is_file($file)
            && is_file($hashFile)
            && trim((string) file_get_contents($hashFile)) === $hash;

        if (!$cached && !mmhApiGenerateCoverJpeg($kind, $slug, $svg)) {
            return new Kirby\Cms\Response(
                'Cover could not be rasterized. Check that the Imagick PHP extension is installed.',
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
