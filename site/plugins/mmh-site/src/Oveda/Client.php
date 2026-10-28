<?php

declare(strict_types=1);

namespace Mmh\Site\Oveda;

use Kirby\Http\Remote;

/**
 * Access to the Oveda event API (https://oveda.de): cached GET requests,
 * the paged event-date search and small helpers shared by the event code.
 * Moved from controllers/oveda-event.php and controllers/events-api.php.
 */
final class Client
{
    public const API = 'https://oveda.de/api/v1';
    public const BASE = 'https://oveda.de';

    public static function timezone(): \DateTimeZone
    {
        return new \DateTimeZone('Europe/Berlin');
    }

    /**
     * Cached GET against the Oveda API. Returns null on any transport or
     * decoding problem, so callers only have to check for null.
     */
    public static function get(string $url, int $ttl = 15): array|null
    {
        $cache = kirby()->cache('oveda');
        $cacheKey = 'get/' . hash('sha256', $url);
        $cached = $cache->get($cacheKey);

        if (is_array($cached) === true) {
            return $cached['data'] ?? null;
        }

        try {
            $response = Remote::get($url);

            if ($response->code() !== 200) {
                $cache->set($cacheKey, ['data' => null], 1);

                return null;
            }

            $data = $response->json();
        } catch (\Throwable $e) {
            return null;
        }

        if (is_array($data) === false) {
            return null;
        }

        $cache->set($cacheKey, ['data' => $data], $ttl);

        return $data;
    }

    /**
     * A single event date. Only carries start/end plus an event stub, which is
     * why the full event has to be fetched separately.
     */
    public static function eventDateById(int $id): array|null
    {
        return self::get(self::API . '/event-dates/' . $id);
    }

    public static function eventById(int $id): array|null
    {
        return self::get(self::API . '/events/' . $id);
    }

    public static function placeById(int $id): array|null
    {
        return self::get(self::API . '/places/' . $id, 3600);
    }

    /**
     * `/events/{id}` returns the place without its location, while the search
     * endpoint and `/places/{id}` both include street and coordinates. Resolve
     * the place separately whenever the location is missing.
     */
    public static function resolvePlace(array $place): array
    {
        if (is_array($place['location'] ?? null) === true) {
            return $place;
        }

        $placeId = (int) ($place['id'] ?? 0);

        if ($placeId <= 0) {
            return $place;
        }

        $resolved = self::placeById($placeId);

        return is_array($resolved) === true ? $resolved : $place;
    }

    public static function absoluteUrl(string|null $path): string|null
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        return str_starts_with($path, '/') ? self::BASE . $path : $path;
    }

    public static function cleanString(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    /**
     * @return array{
     *   items: array,
     *   has_next: bool,
     *   total: int|null,
     *   page: int,
     *   per_page: int,
     *   error: string|null,
     *   rate_limit: array
     * }
     */
    public static function emptyEventDatePage(
        int $pageNumber,
        int $perPage,
        string|null $error = null,
        array $rateLimit = [],
    ): array {
        return [
            'items' => [],
            'has_next' => false,
            'total' => 0,
            'page' => $pageNumber,
            'per_page' => $perPage,
            'error' => $error,
            'rate_limit' => $rateLimit,
        ];
    }

    public static function fetchEventDatePage(string $url, int $pageNumber, int $perPage): array
    {
        try {
            $response = Remote::get($url);
            $data = $response->json();
            $rateLimit = self::rateLimitHeaders($response);

            if (is_array($data) === false || is_array($data['items'] ?? null) === false) {
                $message = (string) ($data['name'] ?? $data['message'] ?? 'Invalid response');
                if ($response->code()) {
                    $message .= ' (' . $response->code() . ')';
                }

                return self::emptyEventDatePage($pageNumber, $perPage, $message, $rateLimit);
            }
        } catch (\Throwable $e) {
            return self::emptyEventDatePage($pageNumber, $perPage, $e->getMessage());
        }

        return [
            'items' => array_values($data['items']),
            'has_next' => (bool) ($data['has_next'] ?? false),
            'total' => isset($data['total']) ? (int) $data['total'] : null,
            'page' => $pageNumber,
            'per_page' => $perPage,
            'error' => null,
            'rate_limit' => $rateLimit,
        ];
    }

    public static function rateLimitHeaders(Remote $response): array
    {
        $headers = [];

        foreach ($response->headers() as $key => $value) {
            if (str_starts_with(strtolower($key), 'x-ratelimit-')) {
                $headers[$key] = trim((string) $value);
            }
        }

        return $headers;
    }
}
