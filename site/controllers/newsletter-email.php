<?php

use Kirby\Cms\Page;
use Kirby\Exception\Exception;

/**
 * Converts a relative site path to an absolute URL for external contexts
 * such as email clients.
 */
function mmhAbsoluteUrl(string $url): string
{
    if ($url === '') {
        return '';
    }

    if (preg_match('!^https?://!i', $url) === 1) {
        return $url;
    }

    $kirby = kirby();
    $baseCandidates = [
        $kirby->option('newsletter.email.baseUrl'),
        $kirby->option('url'),
        $kirby->request()->url()->base(),
        site()->url(),
        getenv('DDEV_PRIMARY_URL_WITHOUT_PORT') ?: null,
        getenv('DDEV_PRIMARY_URL') ?: null,
    ];

    foreach ($baseCandidates as $base) {
        $base = is_string($base) ? rtrim($base, '/') : '';

        if (preg_match('!^https?://!i', $base) === 1) {
            return $base . '/' . ltrim($url, '/');
        }
    }

    return url($url);
}

/**
 * Compiles the newsletter MJML snippet to the final email HTML.
 */
function mmhNewsletterHtml(Page $page, array $data = []): string
{
    $compiler = dirname(__DIR__, 2) . '/node_modules/.bin/mjml';

    if (!is_file($compiler) || !is_executable($compiler)) {
        throw new Exception('MJML ist nicht installiert. Bitte `npm install` ausführen.');
    }

    $mjml = snippet('content-types/newsletter/mjml', ['page' => $page] + $data, true);
    $tmp = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
        . 'mmh-newsletter-' . bin2hex(random_bytes(8));
    $input = $tmp . '.mjml';
    $output = $tmp . '.html';

    file_put_contents($input, $mjml);

    $command = escapeshellarg($compiler)
        . ' '
        . escapeshellarg($input)
        . ' -o '
        . escapeshellarg($output)
        . ' --config.validationLevel=strict';

    exec($command . ' 2>&1', $lines, $code);
    $html = is_file($output) ? file_get_contents($output) : false;

    @unlink($input);
    @unlink($output);

    if ($code !== 0 || $html === false || trim($html) === '') {
        throw new Exception(
            'MJML konnte nicht kompiliert werden: ' . implode("\n", $lines),
        );
    }

    return $html;
}
