<?php

declare(strict_types=1);

namespace Mmh\Site;

use Kirby\Cms\App;
use Kirby\Cms\Page;

/**
 * A project follows its status: a finished project ("abgeschlossen") lives in
 * the project archive, every other one under projects. A project step can
 * set the status of its project. Moved from the web plugin's hook.
 */
final class ProjectStatus
{
    private static bool $isMoving = false;

    /** Hook `page.update:after`. */
    public static function afterUpdate(Page $newPage, Page $oldPage): void
    {
        if ($oldPage->intendedTemplate()->name() === 'project_step') {
            self::passStepStatusToProject($newPage);
        }

        if ($newPage->intendedTemplate()->name() === 'project') {
            self::moveProject($newPage, $oldPage);
        }
    }

    /** A step's `project_status_to` becomes the status of its project. */
    private static function passStepStatusToProject(Page $step): void
    {
        if ($step->project_status_to()->isNotEmpty() && ($step->project_status_to() != $step->parent()->project_status())) {
            $step->parent()->update([
                'project_status' => $step->project_status_to(),
            ]);
        }
    }

    private static function moveProject(Page $newPage, Page $oldPage): void
    {
        if (self::$isMoving) {
            return;
        }

        $site = $newPage->site();
        $projectsRoot = $site->find('projects');
        $archiveRoot = $site->find('project-archive');
        $oldRoot = $oldPage->root();

        if (!$projectsRoot || !$archiveRoot) {
            return;
        }

        $status = trim((string) $newPage->project_status()->value());

        if ($status !== '' && $status !== $newPage->project_status()->value()) {
            App::instance()->impersonate('kirby', function () use ($newPage, $status) {
                $newPage->update(['project_status' => $status]);
            });
            $newPage = $newPage->clone([
                'content' => array_replace($newPage->content()->toArray(), ['project_status' => $status]),
            ]);
        }

        $shouldArchive = $status === 'abgeschlossen';
        $isInArchive = $newPage->parent()->id() === $archiveRoot->id();
        $isInProjects = $newPage->parent()->id() === $projectsRoot->id();

        if ($shouldArchive && $isInProjects) {
            self::move($newPage, $archiveRoot, $oldRoot);
        } elseif (!$shouldArchive && $isInArchive) {
            self::move($newPage, $projectsRoot, $oldRoot);
        }
    }

    private static function move(Page $page, Page $target, string $oldRoot): void
    {
        self::$isMoving = true;

        try {
            App::instance()->impersonate('kirby', function () use ($page, $target, $oldRoot) {
                $page->move($target);
                register_shutdown_function(fn () => self::removeGhost($oldRoot));
            });
        } finally {
            self::$isMoving = false;
        }
    }

    /**
     * Moving a project can leave its old folder behind with nothing but a
     * `project.txt` holding the UUID (and an empty `_changes`). That ghost
     * would show up as a second page; nothing else is touched.
     */
    public static function removeGhost(string $root): void
    {
        if (!$root || !is_dir($root)) {
            return;
        }

        $entries = array_values(array_diff(scandir($root) ?: [], ['.', '..']));
        $others = array_values(array_filter($entries, fn ($entry) => $entry !== '_changes'));

        if (count($others) !== 1 || $others[0] !== 'project.txt') {
            return;
        }

        $file = $root . DIRECTORY_SEPARATOR . 'project.txt';
        $content = is_file($file) ? trim((string) file_get_contents($file)) : '';

        if (!preg_match('/^Uuid:\s*\S+$/', $content)) {
            return;
        }

        @unlink($file);
        $changes = $root . DIRECTORY_SEPARATOR . '_changes';

        if (is_dir($changes) && count(array_diff(scandir($changes) ?: [], ['.', '..'])) === 0) {
            @rmdir($changes);
        }

        @rmdir($root);
    }
}
