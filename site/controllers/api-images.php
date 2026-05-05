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

if (!function_exists('mmhApiCoverJpegResponse')) {
    function mmhApiFontPath(): ?string
    {
        $paths = [
            kirby()->root('index') . '/assets/fonts/NimbusSans-Bold.otf',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    function mmhApiHexColor($image, string $hex)
    {
        $hex = ltrim($hex, '#');

        return imagecolorallocate(
            $image,
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        );
    }

    function mmhApiDrawCenteredText($image, string $text, int $size, int $y, $color, ?string $font): void
    {
        if ($font && function_exists('imagettftext')) {
            $box = imagettfbbox($size, 0, $font, $text);
            $width = $box[2] - $box[0];
            imagettftext($image, $size, 0, (int) ((imagesx($image) - $width) / 2), $y, $color, $font, $text);

            return;
        }

        $fontSize = 5;
        $width = imagefontwidth($fontSize) * strlen($text);
        imagestring($image, $fontSize, (int) ((imagesx($image) - $width) / 2), $y, $text, $color);
    }

    function mmhApiWrapText(string $text, int $size, ?string $font, int $maxWidth): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $test = trim($line . ' ' . $word);

            if ($font && function_exists('imagettfbbox')) {
                $box = imagettfbbox($size, 0, $font, $test);
                $width = $box[2] - $box[0];
            } else {
                $width = imagefontwidth(5) * strlen($test);
            }

            if ($width > $maxWidth && $line !== '') {
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

    function mmhApiDrawLogo($image): void
    {
        $logoPath = kirby()->root('index') . '/assets/generated/mmh-logo-white.png';

        if (!is_file($logoPath) || !function_exists('imagecreatefrompng')) {
            return;
        }

        $logo = imagecreatefrompng($logoPath);

        if (!$logo) {
            return;
        }

        imagealphablending($image, true);

        $targetWidth = 280;
        $targetHeight = (int) round(imagesy($logo) * ($targetWidth / imagesx($logo)));
        $targetX = (int) round(imagesx($image) * 0.384);
        $targetY = (int) round(imagesy($image) * 0.2);

        imagecopyresampled(
            $image,
            $logo,
            $targetX,
            $targetY,
            0,
            0,
            $targetWidth,
            $targetHeight,
            imagesx($logo),
            imagesy($logo),
        );
    }

    function mmhApiCoverJpegResponse(string $label, string $title, array $colors): Kirby\Cms\Response
    {
        if (!extension_loaded('gd') || !function_exists('imagejpeg') || !function_exists('imagecreatetruecolor')) {
            return new Kirby\Cms\Response('GD image support is required', 'text/plain', 500);
        }

        $width = 1200;
        $height = 630;
        $image = imagecreatetruecolor($width, $height);

        if (function_exists('imageantialias')) {
            imageantialias($image, true);
        }

        $start = sscanf(ltrim($colors[0], '#'), '%02x%02x%02x');
        $middle = sscanf(ltrim($colors[1], '#'), '%02x%02x%02x');
        $end = sscanf(ltrim($colors[2], '#'), '%02x%02x%02x');

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $from = $ratio < 0.5 ? $start : $middle;
            $to = $ratio < 0.5 ? $middle : $end;
            $localRatio = $ratio < 0.5 ? $ratio * 2 : ($ratio - 0.5) * 2;
            $color = imagecolorallocate(
                $image,
                (int) ($from[0] + ($to[0] - $from[0]) * $localRatio),
                (int) ($from[1] + ($to[1] - $from[1]) * $localRatio),
                (int) ($from[2] + ($to[2] - $from[2]) * $localRatio),
            );
            imageline($image, 0, $y, $width, $y, $color);
        }

        $white = mmhApiHexColor($image, '#ffffff');
        $muted = mmhApiHexColor($image, '#f4efe8');
        $font = mmhApiFontPath();

        mmhApiDrawLogo($image);
        mmhApiDrawCenteredText($image, $label, 60, (int) round($height * 0.7), $white, $font);

        $lines = mmhApiWrapText($title, 26, $font, 900);
        $lineY = (int) round($height * 0.8);
        foreach ($lines as $line) {
            mmhApiDrawCenteredText($image, $line, 28, $lineY, $muted, $font);
            $lineY += 38;
        }

        ob_start();
        imagejpeg($image, null, 88);
        $jpeg = ob_get_clean();

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
