<?php

return function ($page, $site) {

    $teamPage = $site->find('team');

    // Get team members by category
    $staff = $teamPage ? $teamPage->children()->listed()->filterBy('teams', 'staff', ',') : collection("", []);
    $volunteers = $teamPage ? $teamPage->children()->listed()->filterBy('teams', 'volunteer', ',') : collection("", []);
    $partners = $teamPage ? $teamPage->children()->listed()->filterBy('teams', 'partner', ',') : collection("", []);
    $issuers = $teamPage ? $teamPage->children()->listed()->filterBy('teams', 'issuer', ',') : collection("", []);

    return compact('staff', 'volunteers', 'partners', 'issuers');
};
