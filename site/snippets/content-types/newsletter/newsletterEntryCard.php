<?php

/**
 * @var \Kirby\Cms\StructureObject $entry
 * @var string $badge        Badge label, e.g. 'Rückblick'
 * @var string|null $badgeIcon   Icon key for the badge, see snippets/utilities/icon.php
 * @var string|null $badgeTheme  set from $badge, see utilities/badge.php
 * @var string|null $footerText  Footnote line passed from the section (date, location…)
 * @var string|null $footerIcon  Icon key placed in front of $footerText
 */

$badgeThemes = [
  "Rückblick" => "recap",
  "Aktuell" => "current",
  "Vorschau" => "upcoming",
  "Nachrichten" => "news",
];
$badgeTheme = $badgeThemes[$badge] ?? "recap";
$badgeIcon = $badgeIcon ?? null;
$footerText = $footerText ?? null;
$footerIcon = $footerIcon ?? null;

$rawText = $entry->content_text() ? trim($entry->content_text()->value()) : '';
$excerptLimit = 180;
$hasMore = mb_strlen(strip_tags($rawText)) > $excerptLimit;
$modalId = 'nl-modal-' . uniqid();

$entryLink = $entry->link()->isNotEmpty() ? $entry->link()->value() : null;
$entryMailto = $entry->mailto()->isNotEmpty() ? $entry->mailto()->value() : null;
$hasActions = $entryLink || $entryMailto;

$imageFile = ($entry->image()->isNotEmpty()) ? $entry->image()->toFile() : null;

// Footnote line: optional icon, then the text (date, location ...)
$footerLine = function () use ($footerText, $footerIcon) {
    echo '<p class="font-footnote">';
    if ($footerIcon) {
        snippet('utilities/icon', ['name' => $footerIcon, 'size' => 14]);
        echo ' ';
    }
    echo $footerText . '</p>';
};

// E-mail and website buttons, shared by the card and the modal
$contactButtons = function () use ($entryMailto, $entryLink) {
    if ($entryMailto) {
        snippet('utilities/button', ['label' => 'E-Mail', 'icon' => 'mail', 'href' => 'mailto:' . $entryMailto, 'variant' => 'secondary', 'size' => 'small']);
    }
    if ($entryLink) {
        snippet('utilities/button', ['label' => 'Website', 'icon' => 'link', 'href' => $entryLink, 'variant' => 'secondary', 'size' => 'small', 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]);
    }
};

?>
<?php snippet('utilities/card', [
    'variant' => 'entry',
    'class' => 'c-newsletterEntryCard grid-item',
    'attrs' => ['data-span' => '1/3'],
    'orientation' => 'horizontal',
    'image' => $imageFile,
    'imageOptions' => ['alt' => $entry->headline()->value()],
    'placeholder' => false,
    'meta' => function () use ($badge, $badgeIcon, $badgeTheme) {
        ?>
        <?php snippet('utilities/meta-line', slots: true) ?>
          <?php slot('start') ?>
            <?php snippet('utilities/badge', ['label' => esc($badge), 'theme' => $badgeTheme, 'icon' => $badgeIcon]) ?>
          <?php endslot() ?>
        <?php endsnippet() ?>
        <?php
    },
    'title' => $entry->headline(),
    'subtitle' => $entry->subheadline()->isNotEmpty() ? $entry->subheadline() : null,
    'text' => $hasMore ? $entry->content_text()->excerpt($excerptLimit) : $rawText,
    'body' => $footerText ? $footerLine : null,
    'actions' => $hasActions || $hasMore ? function () use ($contactButtons, $hasMore, $modalId) {
        $contactButtons();
        if ($hasMore) {
            snippet('utilities/button', ['label' => 'Mehr lesen', 'variant' => 'secondary', 'size' => 'small', 'attrs' => ['data-modal-open' => $modalId]]);
        }
    } : null,
]) ?>

<?php if ($hasMore) :
    $titleId = $modalId . '-title';

    snippet('shared/modal', [
        'id' => $modalId,
        'modifier' => 'newsletter-entry-modal',
        'hero' => $imageFile,
        'heroAlt' => $entry->headline()->value(),
        'ariaLabel' => $titleId,

        'slotTitle' => function () use ($entry, $badge, $badgeIcon, $badgeTheme, $titleId) {
            ?>
            <div class="mb-3">
              <?php snippet('utilities/meta-line', slots: true) ?>
                <?php slot('start') ?>
                  <?php snippet('utilities/badge', ['label' => esc($badge), 'theme' => $badgeTheme, 'icon' => $badgeIcon]) ?>
                <?php endslot() ?>
              <?php endsnippet() ?>
            </div>
            <h3 class="font-headline font-line-height-narrow mb-2" id="<?= $titleId ?>"><?= $entry->headline() ?></h3>
            <?php if ($entry->subheadline()->isNotEmpty()) : ?>
              <h4 class="font-subheadline font-line-height-narrow mb-3"><?= $entry->subheadline() ?></h4>
            <?php endif ?>
            <?php
        },

        'slotContent' => function () use ($entry) {
            ?>
            <div class="font-body newsletter-entry-modal__text"><?= $entry->content_text()->kt() ?></div>
            <?php
        },

        'slotFooter' => function () use ($footerText, $footerLine, $contactButtons) {
            echo '<div class="newsletter-entry-modal__footer-meta">';
            if ($footerText) {
                $footerLine();
            }
            $contactButtons();
            echo '</div>';
            snippet('utilities/button', ['label' => 'Schließen', 'variant' => 'secondary', 'size' => 'small', 'attrs' => ['data-modal-close' => true]]);
        },
    ]);
endif ?>
