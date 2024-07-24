<?php
/*
Snippet for setting the project status Field. Reusable

Colors:
    bg-gold
    bg-green
    bg-blue
    bg-gray

*/
?>

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
    <div class="badge text-<?=isset($size) ? $size : 'sm'?> bg-<?=$color ?> text-gold-800 border-solid border-2 border-<?=$color?>-400"> <?=$status?> </div>
