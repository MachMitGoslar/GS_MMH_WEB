<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>

<main class="main">
    <?php snippet('content-types/team/teamsSection', data: [
        'staff' => $staff,
        'volunteers' => $volunteers,
        'partners' => $partners,
        'issuers' => $issuers,
        'title' => $page->headline() ?? "Teams & Unterstützer:innen",
        'subtitle' => $page->subheadline(),
    ]); ?>
</main>

<?php snippet('layout/footer'); ?>