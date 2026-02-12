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
 * You can see the method in use in the `note.php` snippet.
 * and in the `site/blueprints/sections/notes.yml` image query
 *
 * We also define a custom date handler here, which keeps date formatting
 * for the published date consistent in templates, snippets and blueprints.
 *
 * More about models: https://getkirby.com/docs/guide/templates/page-models
 */
use Kirby\Cms\Page;
use Kirby\Cms\Site;

class HomePage extends Page
{
    public function cover()
    {
        return $this->content()->cover()->toFile() ?? $this->image();
    }
    public function projects(): Kirby\Cms\Pages
    {
        return $this->site()->page("projects")->children();
    }

    public function project_steps(): array
    {
        $projects = $this->site()->page("projects")->children() ;
        $steps_array = [];
        foreach ($projects as $project) {
            $steps = $project->project_steps()->toStructure();
            # $steps['project'] = $project;
            foreach ($steps as $step) {
                array_push($steps_array, $step);
            }
        }
        // usort($steps_array, function($a, $b) {
        //     return $a->project_start_date - $b->project_start_date;
        // });

        return $steps_array;
    }
}
