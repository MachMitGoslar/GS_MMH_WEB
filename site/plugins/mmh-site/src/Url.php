<?php

declare(strict_types=1);

namespace Mmh\Site;

/**
 * URL helpers for contexts outside a request, such as mail clients. Moved from the former controllers/newsletter-email.php.
 */
final class Url
{
    /**
     * Converts a relative site path to an absolute URL for external contexts
     * such as email clients.
     */
    public static function absolute(string $url): string
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
}
