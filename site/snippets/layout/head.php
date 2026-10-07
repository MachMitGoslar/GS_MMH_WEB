<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if(!$kirby->option('debug')): ?>
        <?php snippet('seo/meta') ?>
    <?php else: ?>
        <?php if (!$kirby->user()) go('/not-allowed', 404) ?>

        <meta name="robots" content="noindex, nofollow" />
        <meta name="description" content=" ************ THIS IS A DEBUG SITE ********* All displayed content is for testing only! ********" />
        <script>
            console.warn("************ THIS IS A DEBUG SITE ********* All displayed content is for testing only! ********");
        </script>
    <?php endif ?>
    
    <?= $slots->head() ?>
    <?= css(mmhStylesheetBundle()) ?>

</head>
<body>
