<?php

use Kirby\Http\Remote;
use Kirby\Toolkit\Escape;

/**
 * Horoskope List (HTML)
 *
 * Fetches the daily Goslarer Horoskope from the n8n webhook and renders
 * them as a simple list page: zodiac SVG on the left, title and date
 * next to it, and a collapsible text below.
 *
 * @var Kirby\Cms\Site $site
 */

$response = Remote::get('https://n8n.stuffdev.de/webhook/goslar-horoscopes')->json();

$date = $response['date'] ?? date('c');
$signs = $response['signs'] ?? [];

// Unicode zodiac glyphs, keyed by the API `sign` field. Used as a
// fallback when the image asset cannot be resolved.
$glyphs = [
    'aries' => '♈',
    'taurus' => '♉',
    'gemini' => '♊',
    'cancer' => '♋',
    'leo' => '♌',
    'virgo' => '♍',
    'libra' => '♎',
    'scorpio' => '♏',
    'sagittarius' => '♐',
    'capricorn' => '♑',
    'aquarius' => '♒',
    'pisces' => '♓',
];

// Goslarer Sternzeichen → PNG asset (/public/assets/pngs/*.png).
$images = [
    'aries' => 'hk_rammelberg.png',     // Röhrender Rammelsberg
    'taurus' => 'hk_bergknappe.png',     // Beharrlicher Bergknappe
    'gemini' => 'hk_haendler.png',       // Munterer Marktmensch
    'cancer' => 'hk_weberin.png',        // Behütende Brusttuchmacherin
    'leo' => 'hk_kaiser.png',         // Kühner Kaiser
    'virgo' => 'hk_kloserschuelerin.png', // Kluge Klosterschülerin
    'libra' => 'hk_ratsherr.png',       // Rationaler Ratsherr
    'scorpio' => 'hk_erzgaenger.png',     // Eigensinniger Erzgänger
    'sagittarius' => 'hk_fernhaendlerin.png', // Findige Fernhändlerin
    'capricorn' => 'hk_zinngiesser.png',    // Zuverlässiger Zinngießer
    'aquarius' => 'hk_muellerin.png',      // Mutige Müllerin
    'pisces' => 'hk_teichgraefin.png',   // Träumerische Teichgräfin
];

$formattedDate = '';
if ($date) {
    $ts = strtotime($date);
    if ($ts) {
        $formattedDate = date('d.m.Y', $ts);
    }
}

?><!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Goslarer Horoskope<?= $formattedDate ? ' – ' . Escape::html($formattedDate) : '' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Cinzel:wght@500;700&family=EB+Garamond:ital,wght@0,400;0,500;1,400&display=swap">
    <style>
        :root {
            --ink: #2a1d0e;
            --ink-soft: #5a4423;
            --parchment: #f4e6c8;
            --parchment-dark: #e9d4a8;
            --gold: #b8860b;
            --gold-bright: #d4a94a;
            --gold-deep: #8a6508;
            --burgundy: #5b1a1a;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 2rem 1rem 4rem;
            font-family: "EB Garamond", Garamond, "Times New Roman", serif;
            font-size: 1.05rem;
            color: var(--ink);
            line-height: 1.65;
            background-color: #e9d4a8;
            background-image:
                radial-gradient(ellipse at top, rgba(255, 240, 200, 0.6), transparent 60%),
                radial-gradient(ellipse at bottom, rgba(139, 94, 23, 0.25), transparent 65%),
                repeating-radial-gradient(circle at 15% 25%, rgba(139, 94, 23, 0.05) 0, rgba(139, 94, 23, 0.05) 2px, transparent 2px, transparent 8px),
                linear-gradient(180deg, #f4e6c8 0%, #e9d4a8 50%, #d9bf82 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .horoskope {
            max-width: 720px;
            margin: 0 auto;
            padding: 2rem 0.3rem 2.5rem;
            background:
                linear-gradient(180deg, rgba(255, 248, 225, 0.85), rgba(244, 230, 200, 0.85));
            border: 1px solid var(--gold-deep);
            box-shadow:
                0 0 0 6px var(--parchment),
                0 0 0 7px var(--gold),
                0 0 0 9px var(--gold-deep),
                0 18px 40px rgba(60, 30, 5, 0.35);
            position: relative;
        }
        .horoskope::before,
        .horoskope::after {
            content: "❦";
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            color: var(--gold-deep);
            font-size: 1.5rem;
        }
        .horoskope::before { top: -0.55rem; background: var(--parchment); padding: 0 0.5rem; }
        .horoskope::after  { bottom: -0.55rem; background: var(--parchment); padding: 0 0.5rem; }
        .horoskope__header {
            text-align: center;
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px double var(--gold-deep);
        }
        .horoskope__header h1 {
            margin: 0 0 0.35rem;
            font-family: "Cinzel Decorative", "Cinzel", "Trajan Pro", serif;
            font-weight: 900;
            font-size: clamp(1.6rem, 4.5vw, 2.3rem);
            letter-spacing: 0.06em;
            color: var(--burgundy);
            text-shadow: 0 1px 0 rgba(255, 240, 200, 0.9), 0 2px 4px rgba(90, 50, 10, 0.25);
        }
        .horoskope__header p {
            margin: 0;
            font-style: italic;
            color: var(--ink-soft);
            letter-spacing: 0.04em;
        }
        .horoskope__empty {
            text-align: center;
            font-style: italic;
            color: var(--ink-soft);
        }
        .horoskope__item {
            display: block;
            padding: 1rem 1.1rem;
            margin-bottom: 0.9rem;
            background:
                linear-gradient(180deg, rgba(255, 248, 225, 0.7), rgba(233, 212, 168, 0.5));
            border: 1px solid var(--gold-deep);
            border-radius: 2px;
            box-shadow:
                inset 0 0 0 1px rgba(255, 240, 200, 0.6),
                inset 0 0 24px rgba(184, 134, 11, 0.12),
                0 2px 6px rgba(60, 30, 5, 0.15);
            position: relative;
        }
        .horoskope__item::before {
            content: "";
            position: absolute;
            inset: 3px;
            border: 1px solid rgba(184, 134, 11, 0.35);
            pointer-events: none;
            border-radius: 1px;
        }
        .horoskope__summary {
            list-style: none;
            display: grid;
            grid-template-columns: 96px 1fr;
            align-items: center;
            gap: 1.1rem;
            cursor: pointer;
            position: relative;
        }
        .horoskope__summary::-webkit-details-marker { display: none; }
        .horoskope__item[open] .horoskope__summary {
            margin-bottom: 0.9rem;
        }
        .horoskope__portrait {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            display: block;
            position: relative;
            background:
                radial-gradient(circle at 30% 25%, rgba(255, 248, 220, 0.9), rgba(233, 212, 168, 0.6) 55%, rgba(184, 134, 11, 0.25) 100%);
            box-shadow:
                inset 0 0 0 2px var(--gold-deep),
                inset 0 0 0 4px var(--parchment),
                inset 0 0 0 6px var(--gold),
                inset 0 0 14px rgba(138, 101, 8, 0.35),
                0 2px 5px rgba(60, 30, 5, 0.3);
        }
        .horoskope__portrait img {
            position: absolute;
            inset: 10px;
            width: calc(100% - 20px);
            height: calc(100% - 20px);
            object-fit: contain;
            border-radius: 50%;
            filter: drop-shadow(0 1px 1px rgba(90, 50, 10, 0.3));
        }
        .horoskope__glyph {
            position: absolute;
            right: -4px;
            bottom: -4px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            color: var(--parchment);
            background: var(--gold-deep);
            box-shadow: inset 0 0 0 1px var(--gold-bright), 0 1px 2px rgba(60, 30, 5, 0.35);
        }
        .horoskope__title {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }
        .horoskope__title strong {
            font-family: "Cinzel", "Trajan Pro", serif;
            font-weight: 700;
            font-size: min(0.8em, 70vw) 1.2em ;
            line-height: 1.2;
            letter-spacing: 0.04em;
            color: var(--burgundy);
        }
        .horoskope__title span {
            font-size: 0.95rem;
            font-style: italic;
            color: var(--ink-soft);
        }
        .horoskope__text {
            margin: 0;
            padding-top: 0.9rem;
            border-top: 1px double var(--gold-deep);
            white-space: pre-line;
            font-size: 1.05rem;
            color: var(--ink);
        }
        .horoskope__text::first-letter {
            font-family: "Cinzel Decorative", "Cinzel", serif;
            font-weight: 900;
            font-size: 2.6rem;
            float: left;
            line-height: 0.9;
            padding: 0.2rem 0.5rem 0 0;
            color: var(--burgundy);
            text-shadow: 0 1px 0 rgba(255, 240, 200, 0.9), 0 2px 3px rgba(90, 50, 10, 0.3);
        }
        .horoskope__colophon {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px double var(--gold-deep);
            text-align: center;
            font-size: 0.95rem;
            font-style: italic;
            color: var(--ink-soft);
            line-height: 1.6;
        }
        .horoskope__colophon::before {
            content: "❦ ❦ ❦";
            display: block;
            font-style: normal;
            color: var(--gold-deep);
            letter-spacing: 0.6em;
            margin-bottom: 0.75rem;
            padding-left: 0.6em;
        }
        .horoskope__colophon strong {
            font-family: "Cinzel", "Trajan Pro", serif;
            font-style: normal;
            font-weight: 700;
            color: var(--burgundy);
            letter-spacing: 0.04em;
        }
        .horoskope__colophon .horoskope__disclaimer {
            display: block;
            margin-top: 0.6rem;
            font-size: 0.85rem;
            color: var(--ink-soft);
            opacity: 0.85;
        }
    </style>
</head>
<body>
    <div class="horoskope">
        <header class="horoskope__header">
            <h1>Goslarer Horoskope</h1>
            <?php if ($formattedDate) : ?>
                <p>für den <?= Escape::html($formattedDate) ?></p>
            <?php endif ?>
        </header>

        <?php if (empty($signs)) : ?>
            <p>Zurzeit sind keine Horoskope verfügbar.</p>
        <?php else : ?>
            <?php
            // Keep the API's configured order (aries → pisces).
            usort($signs, function ($a, $b) {
                return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
            });
            ?>
            <?php foreach ($signs as $sign) : ?>
                <?php
                $key = $sign['sign'] ?? '';
                $glyph = $glyphs[$key] ?? '✶';
                $imageFile = $images[$key] ?? null;
                $imageUrl = $imageFile ? url('assets/pngs/' . $imageFile) : null;
                $goslar = $sign['goslar'] ?? '';
                $german = $sign['german'] ?? '';
                $span = $sign['span'] ?? '';
                $text = $sign['text'] ?? '';
                ?>
                <details class="horoskope__item">
                    <summary class="horoskope__summary">
                        <span class="horoskope__portrait" aria-hidden="true">
                            <?php if ($imageUrl) : ?>
                                <img src="<?= Escape::attr($imageUrl) ?>" alt="">
                            <?php endif ?>
                            <span class="horoskope__glyph"><?= $glyph ?></span>
                        </span>
                        <span class="horoskope__title">
                            <strong><?= Escape::html($goslar) ?></strong>
                            <!-- <span><?= Escape::html($german) ?> --> <?= $span ? Escape::html($span) : '' ?></span> 
                        </span>
                    </summary>
                    <p class="horoskope__text"><?= Escape::html($text) ?></p>
                </details>
            <?php endforeach ?>
        <?php endif ?>

        <footer class="horoskope__colophon">
            Die <strong>Goslarskope</strong> entstanden beim Hackathon
            <strong>„quelloffen“</strong> als augenzwinkerndes Stadt­projekt
            rund um Goslars Geschichten, Figuren und Wahrzeichen.
            <span class="horoskope__disclaimer">
                Hinweis: Die Horoskope folgen <em>keiner</em> astrologischen
                Grundlage – sie sind frei erfunden und rein zur Unterhaltung
                gedacht.
            </span>
        </footer>
    </div>
</body>
</html>
