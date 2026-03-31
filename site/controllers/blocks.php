<?php

return function ($block) {

    $timezone = new DateTimeZone(kirby()->option('date.timezone', 'Europe/Berlin'));
    $now = (new DateTimeImmutable('now', $timezone))->getTimestamp();

    $publish = null;
    if ($block->publish_date()->isNotEmpty()) {
        $publishValue = $block->publish_date()->toDate('Y-m-d H:i');
        $publishDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', $publishValue, $timezone);
        $publish = $publishDate ? $publishDate->getTimestamp() : null;
    }

    $end = null;
    if ($block->end_date()->isNotEmpty()) {
        $endValue = $block->end_date()->toDate('Y-m-d H:i');
        $endDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', $endValue, $timezone);
        $end = $endDate ? $endDate->getTimestamp() : null;
    }

    $isPreview = kirby()->request()->get('preview') !== null;

    if ($isPreview) {
        return true;
    }

    if (($publish && $publish > $now) || ($end && $end < $now)) {
        return false;
    }

    return true;
};
