<?php

/**
 * Badge for a project status.
 *
 * @var string $status Status text, e.g. "aktiv"
 * @var string|null $size regular (default) or large
 */

$status = trim((string) $status);
$color = getProjectStatusColor($status);

snippet('utilities/badge', [
    'label' => esc($status),
    'theme' => $color === 'false' ? 'default' : 'project-' . $color,
    'size' => $size ?? 'regular',
]);
