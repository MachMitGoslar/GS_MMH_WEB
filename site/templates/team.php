<?php snippet('general/head'); ?>
<?php snippet('general/header'); ?>

<main class="main">
    <?php snippet('components/team/teamsSection', data: [
        'staff' => $staff,
        'volunteers' => $volunteers,
        'partners' => $partners,
        'issuers' => $issuers,
        'title' => $page->headline() ?? "Teams & Unterstützer:innen",
        'subtitle' => $page->subheadline(),
    ]); ?>
</main>

<?php snippet('general/footer'); ?>