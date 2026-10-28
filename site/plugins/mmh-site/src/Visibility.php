<?php

declare(strict_types=1);

namespace Mmh\Site;

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Layout;
use Kirby\Cms\Page;
use Kirby\Content\Content;

/**
 * Timed content: pages, layouts and blocks share one question, "may this be
 * shown now?" (publish_date / end_date). Editors, admins and `?preview` see
 * everything. Registered as `isVisible()` on blocks, layouts and pages
 * (see extensions.php), so templates never check dates themselves.
 */
final class Visibility
{
    /**
     * Whether timed content should be visible in the current request.
     *
     * @param object $content Page, layout or block with optional publish/end date fields
     */
    public static function isVisible(object $content): bool
    {
        $kirby = \kirby();

        if (($kirby instanceof App) === false) {
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

        $timezone = new \DateTimeZone($kirby->option('date.timezone', 'Europe/Berlin'));
        $now = (new \DateTimeImmutable('now', $timezone))->getTimestamp();

        $fields = self::fields($content);

        $toTimestamp = static function (string $name) use ($fields, $content, $timezone): ?int {
            // Pages, blocks and layouts serve their fields through __call(), so
            // method_exists() cannot be used to detect them.
            $field = $fields?->get($name)
                ?? (method_exists($content, $name) ? $content->{$name}() : null);

            if ($field === null || $field->isEmpty()) {
                return null;
            }

            $formatted = $field->toDate('Y-m-d H:i');
            $date = is_string($formatted)
                ? \DateTimeImmutable::createFromFormat('Y-m-d H:i', $formatted, $timezone)
                : false;

            return $date ? $date->getTimestamp() : null;
        };

        $publish = $toTimestamp('publish_date');
        $end = $toTimestamp('end_date');

        if (($publish && $publish > $now) || ($end && $end < $now)) {
            return false;
        }

        return true;
    }

    /**
     * The content that holds the publish/end date fields of a page, block or
     * layout. Layouts keep them in their attributes, everything else in its
     * content. Null for objects that have neither.
     */
    public static function fields(object $content): ?Content
    {
        return match (true) {
            $content instanceof Layout => $content->attrs(),
            $content instanceof Block, $content instanceof Page => $content->content(),
            default => null,
        };
    }
}
