<?php 

    /*** Render Status Badge ****
     * var $status: string
     * 
    */
    $color = getColor($status);

?>


<div class="status-badge mb-2" data-color="<?= $color ?>">
    <?= $status ?>
</div>