<?php

/**
 * This config file is for local ddev usage
 * usually you'd like to turn off debugging on the /config.php file and activate it for local or development sites
 * same foes for installing the panel (creating accounts)
 */
return [
    'debug' => true,
    'panel' => [
        'install' => true
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
