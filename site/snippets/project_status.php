<?php
/*
Snippet for setting the project status Field. Reusable
*/
?>
<header class="grid">
    <div class="column" style="--columns: 2">
    <span> <small> Projektstatus: </small> </span>

  <?php 
    if ($page->project_status()->isNotEmpty()) {
        $status = $page->project_status()->toString();
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
    
    }

    ?>
    <span class="badge bg-<?=$color?> border-solid border-2 border-<?=$color?>-400"> <?=$status?> </span>
    </div>
</header>
