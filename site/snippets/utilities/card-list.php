<?php

/**
 * Card list: pages as a grid of cards, each in the card that fits its page
 * type (all of them are built on utilities/card). Used by the block "Kartenliste" and by templates,
 * so a list of pages is rendered the same way everywhere.
 *
 * @var \Kirby\Cms\Pages $pages
 * @var int|null $columns Cards per row, 2 to 4 (default 3)
 * @var string|null $featured none (default), first (the first card highlighted) or all.
 *                            Cards that know a featured look (note, newsletter) use it.
 */

$columns ??= 3;
$featured ??= 'none';

if ($pages->isEmpty()) {
    return;
}
// template => [card snippet, variable name the snippet expects]
$cards = [
    'project' => ['content-types/projects/projectTeaserCard', 'project'],
    'theme' => ['content-types/themes/themeTeaserCard', 'theme'],
    'note' => ['content-types/notes/noteCard', 'note'],
    'newsletter' => ['content-types/newsletter/newsletterItem', 'newsletter'],
    'member' => ['content-types/team/teamMemberCard', 'teamMember'],
];
?>
<ul class="grid c-card-list" data-columns="<?= (int) $columns ?>">
  <?php foreach ($pages as $page) : ?>
        <?php
        [$snippet, $name] = $cards[$page->intendedTemplate()->name()] ?? ['utilities/page-card', 'page'];
      snippet($snippet, [
      $name => $page,
      'tag' => 'li',
      'featured' => $featured === 'all' || ($featured === 'first' && $page === $pages->first()),
      ]);
      ?>
  <?php endforeach ?>
</ul>
