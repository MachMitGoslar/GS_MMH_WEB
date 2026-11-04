<?php

/**
 * The site's own operational logic (see docs/CLEANUP.md, phase 4 and 7).
 * Lives in the main repository, unlike the separate plugins.
 * Might be a starting point to reintegrate distributed logic back from gs-mmh-web-repository.
 */

Kirby::plugin('gs-mmh/mmh-site', require __DIR__ . '/extensions.php');
