<?php

/**
 * Site Hooks Configuration
 *
 * Define custom hooks for the MachMit!Haus website
 */

use Kirby\Cms\Page;

return [
    /**
     * Auto-update parent project status when project_step is updated
     *
     * When a project step sets a new status, automatically update
     * the parent project's status to match.
     */
    'page.update:after' => function (Page $newPage, Page $oldPage) {
        if ($oldPage->intendedTemplate()->name() == 'project_step') {
            if ($newPage->project_status_to()->isNotEmpty() && ($newPage->project_status_to() != $newPage->parent()->project_status())) {
                $newPage->parent()->update([
                    "project_status" => $newPage->project_status_to(),
                ]);
            }
        }
    },

    /**
     * Auto-set publish date when content is first published
     *
     * Automatically sets the publish date for newsletters and notes
     * when they are published (listed) for the first time.
     */
    'page.changeStatus:after' => function (Page $newPage, Page $oldPage) {
        // Auto-set publish date for newsletters when published for the first time
        if ($newPage->intendedTemplate()->name() === 'newsletter') {
            // Check if page is being published (listed) and doesn't have a publish date yet
            if ($newPage->status() === 'listed' &&
                $oldPage->status() !== 'listed' &&
                $newPage->published()->isEmpty()) {
                $newPage->update([
                    'published' => date('Y-m-d'),
                ]);
            }
        }

        // Auto-set publish date for notes when published for the first time
        if ($newPage->intendedTemplate()->name() === 'notes') {
            // Check if page is being published (listed) and doesn't have a publish date yet
            if ($newPage->status() === 'listed' &&
                $oldPage->status() !== 'listed' &&
                $newPage->published()->isEmpty()) {
                $newPage->update([
                    'published' => date('Y-m-d'),
                ]);
            }
        }
    },
];
