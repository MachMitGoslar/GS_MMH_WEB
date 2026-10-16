<?php

/**
 * Badge for a project step's status.
 *
 * A step can carry a start state, an end state, or both. With both set it is
 * a change, not two independent states, so it renders as a single badge with
 * a gradient from the start to the end color and an arrow between the labels.
 *
 * @var string $from
 * @var string $to
 * @var string|null $size regular (default) or large
 */

$from = trim((string) $from);
$to = trim((string) $to);
$size ??= 'regular';

if ($from === '' && $to === '') {
    return;
}

if ($from === '' || $to === '') {
    snippet('content-types/projects/statusBadge', ['status' => $from ?: $to, 'size' => $size]);

    return;
}

$theme = fn (string $status) => 'project-' . getProjectStatusColor($status);

snippet('utilities/badge', [
    'label' => esc($from),
    'to' => esc($to),
    'theme' => $theme($from),
    'themeTo' => $theme($to),
    'size' => $size,
]);
