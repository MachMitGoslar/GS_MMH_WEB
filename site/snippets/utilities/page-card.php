<?php
/**
 * Page card snippet
 * Same markup and styling as the content card, for whole pages
 * @var \Kirby\Cms\Page $page
 * @var string $class
 * @var string $dataSpan
 */

$dataSpan = $dataSpan ?? '1/1';
$class = trim('content-card ' . ($class ?? 'grid-item'));
$cover = $page ? $page->cover() : null;
$withChildren = $withChildren ?? false;
?>

<article data-span="<?= $dataSpan ?>" class="<?= $class ?>">
    <div class="content-card__content">
        <div class="content-card__image content-card__image--left">
            <?php if ($cover && $cover->isNotEmpty()) : ?>
                <?php snippet('utilities/image', [
                    'file' => $cover,
                    'role' => 'card',
                    'sizes' => '(min-width: 768px) 33vw, 100vw',
                    'alt' => (string) $cover->alt(),
                ]) ?>
            <?php else : ?>
                <?php snippet('utilities/imagePlaceholder') ?>
            <?php endif ?>
        </div>

        <div class="content-card__text">
            <div class="content-card__title">
                <a href="<?= $page->url() ?>" class="content-card__title-link"><?= $page->title()->html() ?></a>
            </div>

            <?php if ($page->subheadline()->isNotEmpty()) : ?>
                <div class="content-card__subtitle"><?= $page->subheadline()->html() ?></div>
            <?php endif ?>

            <?php if ($page->content_text()->isNotEmpty()) : ?>
                <div class="content-card__description"><?= $page->content_text()->kt() ?></div>
            <?php endif ?>

            <?php if ($page->date()->isNotEmpty()) : ?>
                <div class="content-card__date"><?= $page->date()->toDate('d.m.Y') ?></div>
            <?php endif ?>

            <?php if ($page->children()->isNotEmpty() && $withChildren) : ?>
                <div class="content-card__footer">
                    <ul class="content-card__list">
                        <?php foreach ($page->children() as $child) : ?>
                            <a href="<?= $child->url() ?>"><li class="content-card__list-item"><?= $child->title()->html() ?></li></a>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>
        </div>
    </div>
</article>
