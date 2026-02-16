<?php
$target = $_SERVER['HTTP_REFERER'] ?? $page->url() ?? url('/');

header("Refresh: 0; url=" . $target);
?>

<div class="dreamform-success">
    <p>Thank you for your submission!</p>
    <p>You will be redirected shortly…</p>
</div>
