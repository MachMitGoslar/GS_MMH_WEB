<?php
/**
 * Block: Two Column Table
 *
 * Variants: table (default) and hours (opening hours: a card with the clock,
 * days left and times right; a row without a left cell is a note to the row above).
 * "auto" picks hours on the contact page and table everywhere else.
 *
 * @var \Kirby\Cms\Block $block
 */

$variant = $block->variant()->or('auto')->value();
if ($variant === 'auto') {
    $parent = $block->parent();
    $variant = ($parent instanceof \Kirby\Cms\Page && $parent->intendedTemplate()->name() === 'contact') ? 'hours' : 'table';
}
$rows = $block->rows()->isNotEmpty() ? $block->rows()->toStructure() : null;
?>
<?php if ($variant === 'hours') : ?>
<section class="c-hours">
    <div class="c-hours__head">
        <span class="c-hours__icon"><?php snippet('utilities/icon', ['name' => 'clock', 'size' => 22]) ?></span>
        <?php if ($block->title()->isNotEmpty()) : ?>
            <h3 class="c-hours__title font-headline"><?= esc(rtrim((string) $block->title(), ':')) ?></h3>
        <?php endif ?>
    </div>
    <?php if ($rows) : ?>
        <dl class="c-hours__list">
            <?php foreach ($rows as $row) : ?>
                <div class="c-hours__row"<?= $row->col1()->isEmpty() ? ' data-note' : '' ?>>
                    <dt><?= esc($row->col1()) ?></dt>
                    <dd><?= esc($row->col2()) ?></dd>
                </div>
            <?php endforeach ?>
        </dl>
    <?php endif ?>
</section>
<?php else : ?>
<section class="two-column-table">

    <?php if ($block->title()->isNotEmpty()) : ?>
        <h3 class="font-subheadline mb-3">
            <?= esc($block->title()) ?>
        </h3>
    <?php endif ?>

    <?php if ($rows) : ?>
        <table data-span="1/1">
            <tbody>
            <?php foreach ($rows as $row) : ?>
                <tr>
                    <td class="col1" data-span="1/2"><?= esc($row->col1()) ?></td>
                    <td class="col2" data-span="1/2"><?= esc($row->col2()) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

</section>
<?php endif ?>
