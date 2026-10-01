<?php
/**
 * Inline-Zurück-Link im Seiteninhalt (nur ab Desktop-Breite sichtbar; mobil
 * übernimmt der Zurück-Button in der Headerbar, siehe layout/header).
 *
 * @var \Kirby\Cms\Page $page
 * @var array{url?: string, label?: string}|false|null $back  siehe mmhBackLink()
 * @var string|null $class  zusätzliche CSS-Klasse
 */

$backLink = mmhBackLink($page ?? null, $back ?? null);

if (!$backLink) {
    return;
}
?>
<a class="c-back-link font-footnote gs-c-icon-text<?= !empty($class) ? ' ' . esc($class, 'attr') : '' ?>" href="<?= esc($backLink['url'], 'attr') ?>">
  <?php snippet('utilities/icon', ['name' => 'arrow-left', 'size' => 18]) ?>
  <span><?= esc($backLink['label']) ?></span>
</a>
