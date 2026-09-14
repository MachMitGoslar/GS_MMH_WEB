<?php

/**
 * @var \Kirby\Cms\StructureObject $entry
 * @var string $badge        Badge label, e.g. 'Rückblick'
 * @var string|null $badgeIcon   Icon key for the badge, see snippets/utilities/icon.php
 * @var string|null $badgeColor  data-color value for status-badge, e.g. 'active'
 * @var string|null $footerText  Footnote line passed from the section (date, location…)
 * @var string|null $footerIcon  Icon key placed in front of $footerText
 */

$badgeColor = $badgeColor ?? null;
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

?>
<li class="c-newsletterTeaserCard grid-item" data-span="1/3">
  <?php if ($imageFile) : ?>
    <div>
      <img class="hero" src="<?= $imageFile->url() ?>" alt="<?= $entry->headline() ?>" style="object-position: <?= $imageFile->focus()->isNotEmpty() ? $imageFile->focus() : '50% 50%' ?>"  >
    </div>
  <?php endif ?>
  <div class="content">
    <div class="statusheader mb-2">
      <div class="status-badge"<?= $badgeColor ? ' data-color="' . $badgeColor . '"' : '' ?>><?php if ($badgeIcon) : ?><?php snippet('utilities/icon', ['name' => $badgeIcon, 'size' => 14]) ?> <?php endif ?><?= $badge ?></div>
    </div>
    <h3 class="font-headline font-line-height-narrow mb-2"><?= $entry->headline() ?></h3>
    <?php if ($entry->subheadline()->isNotEmpty()) : ?>
      <h4 class="font-subheadline font-line-height-narrow mb-2"><?= $entry->subheadline() ?></h4>
    <?php endif ?>
    <p class="font-footnote"><?= $hasMore ? $entry->content_text()->excerpt($excerptLimit) : $rawText ?></p>
    <?php if ($footerText) : ?>
      <p class="font-footnote mt-2"><?php if ($footerIcon) : ?><?php snippet('utilities/icon', ['name' => $footerIcon, 'size' => 14]) ?> <?php endif ?><?= $footerText ?></p>
    <?php endif ?>
    <?php if ($hasActions || $hasMore) : ?>
      <div class="newsletter-entry-card__actions mt-3">
        <?php if ($entryMailto) : ?>
          <a href="mailto:<?= $entryMailto ?>" class="gs-c-btn" data-type="secondary" data-size="small"><?php snippet('utilities/icon', ['name' => 'mail', 'size' => 16]) ?> E-Mail</a>
        <?php endif ?>
        <?php if ($entryLink) : ?>
          <a href="<?= $entryLink ?>" class="gs-c-btn" data-type="secondary" data-size="small" target="_blank" rel="noopener"><?php snippet('utilities/icon', ['name' => 'link', 'size' => 16]) ?> Website</a>
        <?php endif ?>
        <?php if ($hasMore) : ?>
          <button class="gs-c-btn" data-type="secondary" data-size="small" onclick="document.getElementById('<?= $modalId ?>').showModal()">Mehr lesen</button>
        <?php endif ?>
      </div>
    <?php endif ?>
  </div>
</li>

<?php if ($hasMore) :
    $titleId = $modalId . '-title';

    snippet('shared/modal', [
        'id' => $modalId,
        'modifier' => 'newsletter-entry-modal',
        'hero' => $imageFile,
        'heroAlt' => $entry->headline()->value(),
        'ariaLabel' => $titleId,

        'slotTitle' => function () use ($entry, $badge, $badgeIcon, $badgeColor, $titleId) {
            ?>
            <div class="statusheader mb-3">
              <div class="status-badge"<?= $badgeColor ? ' data-color="' . $badgeColor . '"' : '' ?>><?php if ($badgeIcon) : ?><?php snippet('utilities/icon', ['name' => $badgeIcon, 'size' => 14]) ?> <?php endif ?><?= $badge ?></div>
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

        'slotFooter' => function () use ($entry, $entryLink, $entryMailto, $footerText, $footerIcon) {
            ?>
            <div class="newsletter-entry-modal__footer-meta">
              <?php if ($footerText) : ?>
                <p class="font-footnote"><?php if ($footerIcon) : ?><?php snippet('utilities/icon', ['name' => $footerIcon, 'size' => 14]) ?> <?php endif ?><?= $footerText ?></p>
              <?php endif ?>
              <?php if ($entryMailto) : ?>
                <a href="mailto:<?= $entryMailto ?>" class="gs-c-btn" data-type="secondary" data-size="small"><?php snippet('utilities/icon', ['name' => 'mail', 'size' => 16]) ?> E-Mail</a>
              <?php endif ?>
              <?php if ($entryLink) : ?>
                <a href="<?= $entryLink ?>" class="gs-c-btn" data-type="secondary" data-size="small" target="_blank" rel="noopener"><?php snippet('utilities/icon', ['name' => 'link', 'size' => 16]) ?> Website</a>
              <?php endif ?>
            </div>
            <button class="gs-c-btn" data-type="secondary" data-size="small" onclick="this.closest('dialog').close()">Schließen</button>
            <?php
        },
    ]);
endif ?>
