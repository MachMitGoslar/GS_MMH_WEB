<?php

/**
 * Render a project step's status.
 *
 * A step can carry a start state, an end state, or both. With both set it is
 * a change, not two independent states, so it renders as a single badge with
 * a left-to-right gradient from the start colour to the end colour and an
 * arrow between the two labels — not two separate pills.
 *
 * @var string $from
 * @var string $to
 */

$from = trim((string) $from);
$to = trim((string) $to);

if ($from === '' && $to === '') {
    return;
}

if ($from === '' || $to === '') {
    echo snippet('content-types/projects/statusBadge', ['status' => $from ?: $to]);

    return;
}

$fromColor = getProjectStatusColor($from);
$toColor = getProjectStatusColor($to);

?>
<div
    class="status-badge status-badge--change mb-2"
    style="background: linear-gradient(90deg, var(--color-project-<?= $fromColor ?>), var(--color-project-<?= $toColor ?>));"
>
    <span><?= $from ?></span>
    <svg width="16" height="12" viewBox="0 0 16 12" fill="none" aria-hidden="true">
        <path d="M10 2L14 6L10 10M14 6H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span><?= $to ?></span>
</div>
