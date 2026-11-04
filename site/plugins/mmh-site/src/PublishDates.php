<?php

declare(strict_types=1);

namespace Mmh\Site;

/**
 * Rule for pages that are shown for a limited time (publish_date and
 * end_date, see Visibility): the end must not lie before the start.
 */
final class PublishDates
{
    /**
     * @param array<string, mixed> $values the values being saved
     * @throws \Exception when end_date lies before publish_date
     */
    public static function assertOrder(array $values): void
    {
        $publish = isset($values['publish_date']) ? strtotime((string) $values['publish_date']) : null;
        $end = isset($values['end_date']) ? strtotime((string) $values['end_date']) : null;

        if ($publish && $end && $end < $publish) {
            throw new \Exception('Das Enddatum darf nicht vor dem Veröffentlichungsdatum liegen.');
        }
    }
}
