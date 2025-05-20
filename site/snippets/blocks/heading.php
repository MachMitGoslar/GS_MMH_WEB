<?php /** @var \Kirby\Cms\Block $block */ ?>
<<?= $level = $block->level()->or('h2') ?> class="font-headline mt-2 mb-2" ><?= $block->text() ?></<?= $level ?>>