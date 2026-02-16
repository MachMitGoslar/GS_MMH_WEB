<?php
// Ziel: zurück zur letzten Seite, Fallback = aktuelle Seite oder Startseite
$target = $_SERVER['HTTP_REFERER'] ?? $page->url() ?? url('/');

// Optional: kleine Verzögerung, damit man die Meldung noch sieht
header("Refresh: 2; url=" . $target);
?>

<div class="dreamform-success">
    <p>Thank you for your submission!</p>
    <p>You will be redirected shortly…</p>
</div>
