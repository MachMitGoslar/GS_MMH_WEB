<?php

/**
 * The shared, ergänzbare tag list: every distinct tag used on any project so
 * far, published or archived, listed or draft, sorted alphabetically.
 *
 * Backs the tag autocomplete in `site/blueprints/pages/project.yml` and the
 * tag search on the project overview (`site/controllers/projects.php`).
 * Drafts are included so a tag someone is currently drafting with shows up
 * as a suggestion right away instead of only after publishing.
 */
return function ($site) {
    $projects = new Kirby\Cms\Pages([]);

    foreach (['projects', 'project-archive'] as $root) {
        if ($parent = $site->find($root)) {
            $projects = $projects->merge($parent->childrenAndDrafts());
        }
    }

    $tags = $projects->pluck('tags', ',', true);
    sort($tags, SORT_FLAG_CASE | SORT_STRING);

    return $tags;
};
