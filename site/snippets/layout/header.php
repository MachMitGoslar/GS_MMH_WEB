<?php
/**
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page $page
 */
?>

<?php
$navigation = $site->navigation()?->toPages();
// $back: null = Elternseite (automatisch), false = aus, ['url' =>, 'label' =>] = Override
$backLink = mmhBackLink($page ?? null, $back ?? null);
?>

<header class="header flex" id="mainNavHeader">
    <div class="mobileMenuWrapper">
        <div class="mobileMenuWrapper__start">
            <?php if ($backLink) : ?>
                <a class="header-back" href="<?= esc($backLink['url'], 'attr') ?>" aria-label="<?= esc($backLink['label'], 'attr') ?>">
                    <?php snippet('utilities/icon', ['name' => 'chevron-left', 'size' => 24]) ?>
                </a>
            <?php endif ?>
            <a class="logo font-body font-weight-semiBold block" href="<?=$site->url()?>">
                <?=svg('assets/svg/machmit-logo.svg')?>
            </a>
        </div>
        <button id="menu-toggle" aria-expanded="false" aria-controls="menu" class="hamburger"><?=svg('assets/svg/hamburger.svg')?></button>
    </div>
    <?php if ($navigation) : ?>
        <nav class="mainNav" id="mainNav">
            <ul class="mainNav-list">
                <?php foreach ($navigation as $child) : ?>
                    <li class="mainNav-list-item font-body">
                        <a <?php e($child->isOpen(), ' class="active"') ?> href="<?=$child->url()?>"><?=$child->title()?></a>
                    </li>
                <?php endforeach ?>
            </ul>
        </nav>
    <?php endif; ?>
    <div class="placeholder">

    </div>
</header>