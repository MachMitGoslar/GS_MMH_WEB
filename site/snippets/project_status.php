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
                echo('<p class="badge-green">In Vorbereitung</p>');
                break;
            default:
                 echo('<p class="color-grey">Kein Status</p>');
        }

    }

    ?>
    </div>
</header>
