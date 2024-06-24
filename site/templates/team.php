<?php snippet('header') ?>
<div class="container">

    <?php snippet('intro') ?>
    <?php foreach($tags as $tag): ?>

        <h3> <?= $tag ?> </h3>
        <div class="grid grid-cols-3">
            <?php foreach($page->children()->filterBy('teams', $tag, "," ) as $member): ?>
                <?= snippet('member_card', ['member'=> $member, 'short' => true]) ?>
            <?php endforeach ?>
        </div>
    <?php endforeach;   ?>
</div>


<?php snippet('footer') ?>