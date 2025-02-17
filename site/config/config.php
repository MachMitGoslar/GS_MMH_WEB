<?php

/**
 * The config file is optional. It accepts a return array with config options
 * Note: Never include more than one return statement, all options go within this single return array
 * In this example, we set debugging to true, so that errors are displayed onscreen. 
 * This setting must be set to false in production.
 * All config options: https://getkirby.com/docs/reference/system/options
 */
return [
    'debug' => false,
    'panel' => [
        'install' => false
    ],
    'hooks' => [
        'page.update:after' => function (Kirby\Cms\Page $newPage, Kirby\Cms\Page $oldPage) {
            if($oldPage->intendedTemplate()->name() == 'project_step') {
                print($oldPage->template());
                if($newPage->project_status_to()->isNotEmpty() && ($newPage->project_status_to() != $newPage->parent()->project_status())) {
                    $newPage->parent()->update([
                        "project_status" => $newPage->project_status_to()
                    ]);
                }
            }
        }
    ]

];
