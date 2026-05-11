<?php

/**
 * Site Helper Functions
 *
 * This file contains site-specific helper functions that support
 * the MachMit!Haus website functionality.
 */

use Kirby\Cms\App as KirbyApp;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Exception\Exception;

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
        ->filterBy('project_status', 'abgeschlossen');
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
function mmhNewsletterHtml(Page $page): string
{
    $compiler = dirname(__DIR__) . '/node_modules/.bin/mjml';

    if (!is_file($compiler) || !is_executable($compiler)) {
        throw new Exception('MJML ist nicht installiert. Bitte `npm install` ausführen.');
    }

    $mjml = snippet('content-types/newsletter/mjml', ['page' => $page], true);
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
