<?php

return function ($page, $site) {

    // Get projects where this member is part of the team
    $memberProjects = $site->find('projects')->grandChildren()->listed()->filter(function ($project) use ($page) {
        if (! $project->team()->exists()) {
            return false;
        }

        return $project->team()->toPages()->has($page);
    });

    // Get newsletters authored by this member
    $memberNewsletters = $site->find('newsletter')->children()->listed()->filter(function ($newsletter) use ($page) {
        if (! $newsletter->author()->exists()) {
            return false;
        }

        return $newsletter->author()->toPages()->has($page);
    })->sortBy('publishDate', 'desc');

    // Get blog notes/articles authored by this member
    $memberNotes = $site->find('notes')->children()->listed()->filter(function ($note) use ($page) {
        if (! $note->author() || ! $note->author()->exists()) {
            return false;
        }

        return $note->author()->toPages()->has($page);
    })->sortBy('date', 'desc');

    return compact('memberProjects', 'memberNewsletters', 'memberNotes');
};
