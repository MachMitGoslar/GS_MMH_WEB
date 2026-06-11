<?php

/*** Render Status Badge ****
 * var $status: string
 *
*/

$status = trim((string) $status);
$color = getProjectStatusColor($status);

?>


<div class="status-badge mb-2" data-color="<?= $color ?>">
    <?= $status ?>
</div>
