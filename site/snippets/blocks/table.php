<?php
/**
 * Block: Two Column Table
 *
 * @var \Kirby\Cms\Block $block
 */
?>

<section class="two-column-table">

    <?php if ($block->title()->isNotEmpty()): ?>
        <h3 class="font-subheadline mb-3">
            <?= esc($block->title()) ?>
        </h3>
    <?php endif ?>

    <?php if ($block->rows()->isNotEmpty()): ?>
        <table data-span="1/1">
            <tbody>
            <?php foreach ($block->rows()->toStructure() as $row): ?>
                <tr>
                    <td class="col1" data-span="1/2"><?= esc($row->col1()) ?></td>
                    <td class="col2" data-span="1/2"><?= esc($row->col2()) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

</section>
