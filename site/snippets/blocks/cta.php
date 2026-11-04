<?php

$buttons = $block->buttons()->toBlocks();

?>
    <?= $block->title() ?>
    <p class="font-text mb-2"> <?=$block->description() ?></p>
    <?php foreach ($buttons as $button) : ?>
        <?= $button ?>
    <?php endforeach ?>
    
