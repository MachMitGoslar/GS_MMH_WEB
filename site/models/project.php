<?php

/**
 * Page models extend Kirby's default page object.
 *
 * In page models you can define methods that are then available
 * everywhere in Kirby where you call a page of the extended type.
 *
 * In this example, we define the cover method that either returns
 * an image selected in the cover field or the first image in the folder.
 *
 * You can see the method in use in the `home.php` and `photography.php` templates
 * and in the `site/blueprints/sections/albums.yml` image query
 *
 * More about models: https://getkirby.com/docs/guide/templates/page-models
 */

use Kirby\Panel\Page as PanelPage;

class ProjectPage extends Page
{
    public function cover()
    {
        return $this->content()->get('cover')->toFile() ?? $this->image();
    }

    public function project_steps(): Kirby\Cms\Pages
    {
        return $this->children()->sortBy(
            fn ($step) => $this->projectStepTimestamp($step),
            'desc',
        );
    }

    public function latestProjectStep(): Kirby\Cms\Page|null
    {
        return $this->project_steps()
            ->listed()
            ->filter(fn ($step) => $step->project_status_to()->isNotEmpty())
            ->first();
    }

    public function effectiveProjectStatus(): string
    {
        $status = trim((string) $this->project_status()->value());
        if ($status !== '') {
            return $status;
        }

        $latestStep = $this->latestProjectStep();

        return $latestStep
            ? trim((string) $latestStep->project_status_to()->value())
            : '';
    }

    protected function projectStepTimestamp(Kirby\Cms\Page $step): int
    {
        $date = $step->project_start_date()->value();
        if ($date === '') {
            return 0;
        }

        $time = $step->project_start_time()->or('00:00')->value();
        $timestamp = strtotime($date . ' ' . $time);

        return $timestamp === false ? 0 : $timestamp;
    }

    public function panel(): PanelPage
    {
        return new ProjectPanelPage($this);
    }
}

class ProjectPanelPage extends PanelPage
{
    public function breadcrumb(): array
    {
        $page = $this->model();
        $archive = $page->site()->find('project-archive');
        $projects = $page->site()->find('projects');

        if ($archive && $projects && $page->parent()->id() === $archive->id()) {
            return [
                [
                    'label' => $archive->title()->toString(),
                    'link' => $projects->panel()->url(true) . '?tab=archive',
                ],
                [
                    'label' => $page->title()->toString(),
                    'link' => $this->url(true),
                ],
            ];
        }

        return parent::breadcrumb();
    }
}
