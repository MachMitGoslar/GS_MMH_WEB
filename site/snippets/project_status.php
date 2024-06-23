<?php
/*
Snippet for setting the project status Field. Reusable
*/
?>
<header class="grid grid-cols-2">
    <div class=" col-span-2">

  <?php 
        $status = $project_status->toString();
        switch ($status) {
            case("in Vorbereitung"):
                $color = "green";
                /*$test = "border-green-400";*/
                /*$test = "bg-green";*/
                break;
            case("aktiv"):
                $color = "gold";
                $test = "border-gold-400";

                break;
            default:
                $color = "blue";
        }

    ?>
    <span class="badge text-<?=isset($size) ? $size : 'sm'?> bg-<?=$color ?> border-solid border-2 border-<?=$color?>-400"> <?=$status?> </span>
    </div>
</header>
