<?php

return function ($site) {

    $element = $site
        ->find('projects')
        ->grandChildren()
        ->sortBy("project_start_date", "desc");

    return $element;
};
