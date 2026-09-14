<?php

/**
 * A "Themenfeld" — a topic landing page that gathers the projects assigned to
 * it. Projects are NOT children of this page: they keep living under
 * `projects/` and `project-archive/` and reference the topic by slug through
 * their `topic` field. This page only queries them.
 */
class ThemePage extends Page
{
    public function cover()
    {
        return $this->content()->get('cover')->toFile() ?? $this->image();
    }

    /**
     * All projects assigned to this topic, newest project step first.
     */
    public function projects(): Kirby\Cms\Pages
    {
        $slug = trim((string) $this->topic()->value());

        if ($slug === '') {
            return new Kirby\Cms\Pages([]);
        }

        $all = new Kirby\Cms\Pages([]);
        foreach (['projects', 'project-archive'] as $root) {
            if ($parent = $this->site()->find($root)) {
                $all = $all->merge($parent->children()->listed());
            }
        }

        return $all
            ->filter(fn ($project) => $project->topicSlug() === $slug)
            ->sortBy(fn ($project) => $project->latestStepDate(), 'desc');
    }

    /** Still running — everything that is not `abgeschlossen`. */
    public function activeProjects(): Kirby\Cms\Pages
    {
        return $this->projects()->filter(
            fn ($project) => $project->effectiveProjectStatus() !== 'abgeschlossen',
        );
    }

    public function archivedProjects(): Kirby\Cms\Pages
    {
        return $this->projects()->filter(
            fn ($project) => $project->effectiveProjectStatus() === 'abgeschlossen',
        );
    }

    /** Used by the Panel section, which cannot call a filter closure. */
    public function projectsForPanel(): Kirby\Cms\Pages
    {
        return $this->projects();
    }
}
