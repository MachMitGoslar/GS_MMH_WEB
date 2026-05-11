<?php

/**
 * MJML export for a newsletter page.
 *
 * @var \Kirby\Cms\Page $page
 */

if (!function_exists('mmhNewsletterMjmlText')) {
    function mmhNewsletterMjmlText($field): string
    {
        if (!$field || $field->isEmpty()) {
            return '';
        }

        return $field->kt()->value();
    }
}

if (!function_exists('mmhNewsletterMjmlImageUrl')) {
    function mmhNewsletterMjmlImageUrl($file): string
    {
        if (!$file) {
            return '';
        }

        return mmhAbsoluteUrl($file->url());
    }
}

if (!function_exists('mmhNewsletterMjmlFieldFile')) {
    function mmhNewsletterMjmlFieldFile($field): ?\Kirby\Cms\File
    {
        if ($field instanceof \Kirby\Cms\File) {
            return $field;
        }

        if (!$field || !method_exists($field, 'isNotEmpty') || $field->isNotEmpty() === false) {
            return null;
        }

        return method_exists($field, 'toFile') ? $field->toFile() : null;
    }
}

if (!function_exists('mmhNewsletterMjmlEventDate')) {
    function mmhNewsletterMjmlEventDate($event): string
    {
        if ($event->event_date()->isEmpty()) {
            return '';
        }

        $date = $event->event_date()->toDate('d.m.Y');

        if ($event->event_date_end()->isNotEmpty()) {
            $date .= ' - ' . $event->event_date_end()->toDate('d.m.Y');
        }

        return $date;
    }
}

if (!function_exists('mmhNewsletterMjmlSpacer')) {
    function mmhNewsletterMjmlSpacer(string $height): string
    {
        return '<mj-section padding="0" background-color="#f5f5f5"><mj-column><mj-spacer height="'
            . esc($height, 'attr')
            . '" /></mj-column></mj-section>';
    }
}

if (!function_exists('mmhNewsletterMjmlEntrySection')) {
    function mmhNewsletterMjmlEntrySection($entry, string $label): string
    {
        $image = mmhNewsletterMjmlFieldFile($entry->image());

        ob_start();
        ?>
        <mj-section background-color="#ffffff" border="1px solid #d9d9d9" border-radius="8px" padding="0">
          <mj-column>
            <?php if ($image) : ?>
            <mj-image src="<?= esc(mmhNewsletterMjmlImageUrl($image), 'attr') ?>" alt="<?= esc($entry->headline()->value(), 'attr') ?>" padding="0" fluid-on-mobile="true" />
            <?php endif ?>
            <mj-text padding="18px 18px 8px">
              <span class="badge"><?= esc($label) ?></span>
            </mj-text>
            <mj-text font-size="20px" line-height="1.2" font-weight="700" color="#222222" padding="0 18px 6px"><?= esc($entry->headline()->value()) ?></mj-text>
            <?php if ($entry->subheadline()->isNotEmpty()) : ?>
            <mj-text font-size="16px" line-height="1.4" font-weight="500" color="#4e4e4d" padding="0 18px 8px"><?= esc($entry->subheadline()->value()) ?></mj-text>
            <?php endif ?>
            <mj-text css-class="body-copy" font-size="15px" line-height="1.55" color="#303030" padding="0 18px 18px"><?= mmhNewsletterMjmlText($entry->content_text()) ?></mj-text>
            <?php if ($entry->link()->isNotEmpty()) : ?>
            <mj-button href="<?= esc($entry->link()->value(), 'attr') ?>" background-color="#866811" color="#ffffff" border-radius="999px" font-size="14px" align="left" padding="0 18px 18px">Weiterlesen</mj-button>
            <?php endif ?>
            <?php if ($entry->mailto()->isNotEmpty()) : ?>
            <mj-button href="mailto:<?= esc($entry->mailto()->value(), 'attr') ?>" background-color="#425aa7" color="#ffffff" border-radius="999px" font-size="14px" align="left" padding="0 18px 18px">Kontakt aufnehmen</mj-button>
            <?php endif ?>
          </mj-column>
        </mj-section>
        <?= mmhNewsletterMjmlSpacer('18px') ?>
        <?php

        return ob_get_clean();
    }
}

$author = $page->author()->toPage();
$preview = $page->greeting_text()->isNotEmpty()
    ? $page->greeting_text()->excerpt(160)->value()
    : 'Aktuelles aus dem MachMit!Haus Goslar';
$dayNames = [
    'monday' => 'Mo',
    'tuesday' => 'Di',
    'wednesday' => 'Mi',
    'thursday' => 'Do',
    'friday' => 'Fr',
];

?>
<mjml>
  <mj-head>
    <mj-title><?= esc($page->title()->value()) ?> | MachMit!Haus Newsletter</mj-title>
    <mj-preview><?= esc($preview) ?></mj-preview>
    <mj-font name="Inter" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700" />
    <mj-attributes>
      <mj-all font-family="Inter, Arial, Helvetica, sans-serif" />
      <mj-body background-color="#f5f5f5" />
      <mj-section background-color="#f5f5f5" />
      <mj-text color="#303030" font-size="16px" line-height="1.45" />
      <mj-button font-weight="700" inner-padding="12px 20px" />
    </mj-attributes>
    <mj-style inline="inline">
      a { color: #866811; }
      p { margin: 0 0 12px; }
      ul { margin: 0; padding-left: 18px; }
      .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        background: #866811;
        color: #ffffff;
        font-size: 12px;
        line-height: 1;
        font-weight: 700;
      }
      .event-card {
        border-left: 3px solid #fbc62e;
        background: #fff9e9;
        border-radius: 2px;
        padding: 10px 12px;
      }
      .event-time {
        color: #866811;
        font-size: 12px;
        line-height: 1.3;
        font-weight: 700;
        text-transform: uppercase;
      }
      .section-kicker {
        color: #866811;
        font-size: 13px;
        line-height: 1.2;
        font-weight: 700;
      }
      .body-copy p:last-child {
        margin-bottom: 0;
      }
    </mj-style>
  </mj-head>
  <mj-body width="640px">
    <mj-section background-color="#5d4e37" padding="108px 24px 56px">
      <mj-column>
        <mj-image src="<?= esc(mmhAbsoluteUrl('/assets/generated/mmh-logo-white.png'), 'attr') ?>" alt="MachMit!Haus" width="168px" align="center" padding="0 0 64px" />
        <mj-text align="center" color="#ffffff" font-size="48px" line-height="1.08" font-weight="700" padding="0 0 20px">Newsletter</mj-text>
        <mj-text align="center" color="#fcce4c" font-size="28px" line-height="1.15" font-weight="700" padding="0"><?= esc($page->title()->value()) ?></mj-text>
      </mj-column>
    </mj-section>

    <mj-section background-color="#3f3f3e" border-radius="8px" padding="24px 20px">
      <mj-column>
        <?php if ($author) : ?>
        <mj-text font-size="20px" line-height="1.3" color="#ffffff" font-weight="700" padding="0 0 2px"><?= esc($author->title()->value()) ?></mj-text>
        <mj-text font-size="13px" line-height="1.4" color="#c6c6c6" padding="0 0 18px">Autor</mj-text>
        <?php endif ?>
        <mj-text css-class="body-copy" font-size="15px" line-height="1.6" color="#ececec" font-style="italic" padding="0"><?= mmhNewsletterMjmlText($page->greeting_text()) ?></mj-text>
      </mj-column>
    </mj-section>

    <?= mmhNewsletterMjmlSpacer('32px') ?>

    <?php if ($page->weekly_dates()->isNotEmpty()) : ?>
    <mj-section padding="0 16px 14px">
      <mj-column>
        <mj-text font-size="28px" line-height="1.15" font-weight="700" color="#222222" padding="0">Die Woche im MachMit!Haus</mj-text>
      </mj-column>
    </mj-section>
        <?php foreach ($dayNames as $dayKey => $dayLabel) : ?>
            <?php $dayEvents = $page->weekly_dates()->toStructure()->filterBy('day', $dayKey); ?>
    <mj-section background-color="#ffffff" border="1px solid #d9d9d9" border-radius="8px" padding="16px 16px 6px">
      <mj-column>
        <mj-text align="center" padding="0 0 10px"><span style="display:block;background:#c99b19;color:#ffffff;border-radius:4px;padding:10px;font-size:14px;line-height:1;font-weight:700;"><?= esc($dayLabel) ?></span></mj-text>
            <?php if ($dayEvents->count() === 0) : ?>
        <mj-text align="center" font-size="14px" color="#a2a2a2" font-style="italic" padding="10px 0">Keine Termine</mj-text>
            <?php endif ?>
            <?php foreach ($dayEvents as $event) : ?>
        <mj-text font-size="14px" line-height="1.35" padding="6px 0 10px">
          <div class="event-card">
            <span class="event-time"><?= esc($event->start_time()->toDate('H:i')) ?></span><br />
            <strong><?= esc($event->activity()->value()) ?></strong>
                <?php if ($event->recurring()->isNotEmpty()) :
                    ?><br /><span style="color:#6f6f6e;font-size:12px;"><?= esc($event->recurring()->value()) ?></span><?php
                endif ?>
          </div>
        </mj-text>
            <?php endforeach ?>
      </mj-column>
    </mj-section>
            <?= mmhNewsletterMjmlSpacer('10px') ?>
        <?php endforeach ?>
    <?php endif ?>

    <?php if ($page->upcomming_dates()->isNotEmpty()) : ?>
        <?= mmhNewsletterMjmlSpacer('22px') ?>
    <mj-section padding="0 16px 14px">
      <mj-column>
        <mj-text font-size="28px" line-height="1.15" font-weight="700" color="#222222" padding="0">Kommende Termine</mj-text>
      </mj-column>
    </mj-section>
    <mj-section padding="0">
      <mj-column>
        <?php foreach ($page->upcomming_dates()->toStructure() as $event) : ?>
            <?php if ($event->show_event_date()->toBool() !== true) {
                continue;
            } ?>
        <mj-table padding="0 16px 14px">
          <tr>
            <td style="background:#ffffff;border:1px solid #d9d9d9;border-left:4px solid #fbc62e;border-radius:8px;padding:16px;">
              <div class="section-kicker"><?= esc(mmhNewsletterMjmlEventDate($event)) ?></div>
              <div style="font-size:17px;line-height:1.3;font-weight:700;color:#222222;margin-top:6px;"><?= esc($event->event_name()->value()) ?></div>
              <?php if ($event->event_location()->isNotEmpty()) : ?>
              <div style="font-size:13px;line-height:1.4;color:#7f7f7f;margin-top:8px;"><?= esc($event->event_location()->value()) ?></div>
              <?php endif ?>
            </td>
          </tr>
        </mj-table>
        <?php endforeach ?>
      </mj-column>
    </mj-section>
    <?php endif ?>

    <?php if ($page->timeline()->isNotEmpty()) : ?>
        <?= mmhNewsletterMjmlSpacer('22px') ?>
    <mj-section padding="0 16px 14px">
      <mj-column>
        <mj-text font-size="28px" line-height="1.15" font-weight="700" color="#222222" padding="0">Jahresrückblick</mj-text>
      </mj-column>
    </mj-section>
        <?php foreach ($page->timeline()->toStructure() as $entry) : ?>
            <?php $image = mmhNewsletterMjmlFieldFile($entry->image()); ?>
    <mj-section background-color="#ffffff" border="1px solid #d9d9d9" border-radius="8px" padding="18px 18px 16px">
            <?php if ($image) : ?>
      <mj-column width="32%" vertical-align="top">
        <mj-image src="<?= esc(mmhNewsletterMjmlImageUrl($image), 'attr') ?>" alt="<?= esc($entry->year()->value(), 'attr') ?>" width="96px" border-radius="999px" padding="0 0 14px" />
      </mj-column>
            <?php endif ?>
      <mj-column width="<?= $image ? '68%' : '100%' ?>" vertical-align="top">
        <mj-text font-size="17px" line-height="1.3" color="#866811" font-weight="700" padding="0 0 6px"><?= esc($entry->year()->value()) ?></mj-text>
        <mj-text font-size="15px" line-height="1.55" color="#303030" padding="0"><?= esc($entry->summary()->value()) ?></mj-text>
      </mj-column>
    </mj-section>
            <?= mmhNewsletterMjmlSpacer('12px') ?>
        <?php endforeach ?>
    <?php endif ?>

    <?php foreach (
    [
        'review_entries' => 'Rückblick',
        'actual_entries' => 'Aktuelle Projekte',
        'upcomming_entries' => 'Vorschau',
        'news' => 'Nachrichten aus dem MachMit!Haus',
    ] as $field => $label
) : ?>
      <?php if ($page->{$field}()->isNotEmpty()) : ?>
            <?= mmhNewsletterMjmlSpacer('22px') ?>
    <mj-section padding="0 16px 14px">
      <mj-column>
        <mj-text font-size="28px" line-height="1.15" font-weight="700" color="#222222" padding="0"><?= esc($label) ?></mj-text>
      </mj-column>
    </mj-section>
            <?php foreach ($page->{$field}()->toStructure() as $entry) : ?>
                <?= mmhNewsletterMjmlEntrySection($entry, $label) ?>
            <?php endforeach ?>
      <?php endif ?>
    <?php endforeach ?>

    <?php if ($page->closingsinfos()->isNotEmpty()) : ?>
        <?= mmhNewsletterMjmlSpacer('22px') ?>
    <mj-section background-color="#3f3f3e" border-radius="8px" padding="24px 20px">
      <mj-column>
        <mj-text color="#ffffff" font-size="22px" line-height="1.2" font-weight="700" padding="0 0 12px">Mehr Informationen?</mj-text>
        <mj-text css-class="body-copy" color="#ececec" font-size="15px" line-height="1.55" padding="0"><?= mmhNewsletterMjmlText($page->closingsinfos()) ?></mj-text>
      </mj-column>
    </mj-section>
    <?php endif ?>

    <mj-section padding="28px 18px 34px">
      <mj-column>
        <mj-text font-size="13px" line-height="1.6" color="#6f6f6e" padding="0">
          MachMit!Haus Goslar<br />
          Markt 7, 38640 Goslar<br />
          <a href="mailto:machmit@goslar.de">machmit@goslar.de</a> | <a href="https://machmit.goslar.de">machmit.goslar.de</a>
        </mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
