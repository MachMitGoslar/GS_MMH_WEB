<?php

use Kirby\Cms\Page;

/**
 * Renders the public newsletter page for the HTML download in a mobile layout.
 */
function mmhNewsletterMobileHtml(Page $page, array $data = []): string
{
    $html = $page->render();
    $html = mmhNewsletterRemoveSiteChrome($html);
    $html = mmhNewsletterPrepareSelfContainedNewsletter($html);
    $html = mmhNewsletterNormalizeTimelineMarkup($html);
    $html = mmhNewsletterConvertHeroToEmailTable($html);
    $html = mmhNewsletterReplaceModalButtons($html, $page);
    $html = mmhNewsletterWrapMainForEmail($html);

    if (!empty($data['unsubscribeUrl'])) {
        $html = mmhNewsletterInjectUnsubscribeLink($html, (string) $data['unsubscribeUrl']);
    }

    $html = mmhNewsletterAddMobileExportClass($html);
    $html = mmhNewsletterInjectMobileExportStyles($html);
    $html = mmhNewsletterInlineLocalStylesheets($html);
    $html = mmhNewsletterInlineLocalAssets($html);
    $html = mmhNewsletterInlineCriticalEmailStyles($html);
    $html = mmhNewsletterAbsoluteDocumentUrls($html);

    if (stripos($html, '</body>') === false) {
        $html .= "\n</body>";
    }

    if (stripos($html, '</html>') === false) {
        $html .= "\n</html>";
    }

    return $html;
}

function mmhNewsletterRemoveSiteChrome(string $html): string
{
    $html = preg_replace('/<header\b[^>]*>.*?<\/header>/is', '', $html, 1) ?? $html;
    $html = preg_replace('/<footer\b[^>]*>.*?<\/footer>/is', '', $html, 1) ?? $html;

    return preg_replace('/<script\b[^>]*>\s*const htmlElement = document\.querySelector\(.*?<\/script>/is', '', $html, 1) ?? $html;
}

function mmhNewsletterPrepareSelfContainedNewsletter(string $html): string
{
    $html = preg_replace('/<link\b[^>]*api\.mapbox\.com\/mapbox-gl-js[^>]*>/i', '', $html) ?? $html;
    $html = preg_replace('/<script\b[^>]*api\.mapbox\.com\/mapbox-gl-js[^>]*><\/script>/i', '', $html) ?? $html;
    $html = preg_replace('/<script\b[^>]*>\s*mapboxgl\.accessToken\s*=.*?<\/script>/is', '', $html) ?? $html;
    $html = str_replace('/assets/svg/RZ-RGB_MM!2_iv.svg', '/assets/generated/mmh-logo-white.png', $html);
    $html = preg_replace(
        '/<img class="newsletter-logo" src="\/assets\/generated\/mmh-logo-white\.png" alt="MachMit!Haus Logo">/i',
        '<img class="newsletter-logo" src="/assets/generated/mmh-logo-white.png" alt="MachMit!Haus Logo" width="180" height="180">',
        $html,
        1,
    ) ?? $html;

    return preg_replace(
        '/<div\s+id=(["\'])map\1\s+class=(["\'])mb-4\2>\s*<\/div>/i',
        '<div id="map" class="mb-4 newsletter-static-map">'
            . '<img src="/assets/svg/map_pin.svg" alt="" class="newsletter-static-map__pin">'
            . '<span>Markt 7, 38640 Goslar</span>'
            . '</div>',
        $html,
        1,
    ) ?? $html;
}

function mmhNewsletterNormalizeTimelineMarkup(string $html): string
{
    return preg_replace_callback(
        '/(<div class="timeline-item timeline-item--left">\s*<div class="timeline-item__container">\s*(?:<!--.*?-->\s*)?)(<div class="timeline-content">.*?<\/div>\s*<\/div>\s*)(<div class="timeline-image">.*?<\/div>\s*)(<div class="timeline-connector"><\/div>)/is',
        static fn (array $matches): string => $matches[1] . $matches[4] . "\n" . $matches[3] . "\n" . $matches[2],
        $html,
    ) ?? $html;
}

function mmhNewsletterConvertHeroToEmailTable(string $html): string
{
    return preg_replace_callback(
        '/<section class="newsletter-cover">\s*<div class="newsletter-cover-content">\s*(<img class="newsletter-logo"[^>]+>)\s*(<h1 class="[^"]*\bnewsletter-title\b[^"]*"[^>]*>.*?<\/h1>)\s*(<h2 class="[^"]*\bnewsletter-date\b[^"]*"[^>]*>.*?<\/h2>)\s*<\/div>\s*<\/section>/is',
        static function (array $matches): string {
            $title = preg_replace('/<h1\b/i', '<h1 align="center"', $matches[2], 1) ?? $matches[2];
            $date = preg_replace('/<h2\b/i', '<h2 align="center"', $matches[3], 1) ?? $matches[3];

            return '<table role="presentation" align="center" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;margin:0 auto 40px;">'
                . '<tr>'
                . '<td align="center" style="text-align:center;">'
                . '<table role="presentation" class="newsletter-cover-email" align="center" width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#5d4e37" style="width:600px;max-width:600px;margin:0 auto;background:#5d4e37;background-color:#5d4e37;border-collapse:collapse;table-layout:fixed;">'
                . '<tr>'
                . '<td align="center" bgcolor="#5d4e37" style="background:#5d4e37;background-color:#5d4e37;text-align:center;padding:72px 20px;">'
                . '<table role="presentation" align="center" width="560" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:560px;margin:0 auto;border-collapse:collapse;">'
                . '<tr><td align="center" style="text-align:center;">'
                . $matches[1]
                . '</td></tr>'
                . '<tr><td align="center" style="text-align:center;">'
                . $title
                . '</td></tr>'
                . '<tr><td align="center" style="text-align:center;">'
                . $date
                . '</td></tr>'
                . '</table>'
                . '</td>'
                . '</tr>'
                . '</table>'
                . '</td>'
                . '</tr>'
                . '</table>';
        },
        $html,
        1,
    ) ?? $html;
}

function mmhNewsletterReplaceModalButtons(string $html, Page $page): string
{
    $url = esc(mmhAbsoluteUrl($page->uri()));
    $link = '<a href="' . $url . '" class="gs-c-btn" data-type="secondary" data-size="small" target="_blank" rel="noopener">Mehr lesen</a>';

    $html = preg_replace(
        '/<button\b([^>]*)onclick=(["\'])document\.getElementById\([^)]+\)\.showModal\(\)\2([^>]*)>\s*Mehr lesen\s*<\/button>/i',
        $link,
        $html,
    ) ?? $html;

    return preg_replace('/<dialog\b[^>]*class=(["\'])newsletter-entry-modal\1[^>]*>.*?<\/dialog>/is', '', $html) ?? $html;
}

function mmhNewsletterWrapMainForEmail(string $html): string
{
    $html = preg_replace(
        '/<main class="main">/i',
        '<main class="main"><table role="presentation" align="center" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;margin:0 auto;border-collapse:collapse;"><tr><td align="center" style="text-align:center;">',
        $html,
        1,
    ) ?? $html;

    return preg_replace(
        '/<\/main>/i',
        '</td></tr></table></main>',
        $html,
        1,
    ) ?? $html;
}

function mmhNewsletterInjectUnsubscribeLink(string $html, string $unsubscribeUrl): string
{
    $link = '<section class="grid content mb-7">'
        . '<div class="grid-item" data-span="1/1">'
        . '<p class="font-footnote">'
        . '<a href="' . esc($unsubscribeUrl) . '">Newsletter abbestellen</a>'
        . '</p>'
        . '</div>'
        . '</section>';

    if (stripos($html, '</main>') !== false) {
        return str_ireplace('</main>', $link . "\n</main>", $html);
    }

    return $html . "\n" . $link;
}

function mmhNewsletterAddMobileExportClass(string $html): string
{
    return preg_replace_callback(
        '/<body\b([^>]*)>/i',
        function (array $matches): string {
            $attributes = $matches[1];

            if (preg_match('/\bclass=(["\'])(.*?)\1/i', $attributes, $classMatch) === 1) {
                $classes = trim($classMatch[2] . ' newsletter-mobile-export');
                $attributes = preg_replace(
                    '/\bclass=(["\'])(.*?)\1/i',
                    'class="' . $classes . '"',
                    $attributes,
                    1,
                );
            } else {
                $attributes .= ' class="newsletter-mobile-export"';
            }

            return '<body' . $attributes . '>';
        },
        $html,
        1,
    ) ?? $html;
}

function mmhNewsletterInjectMobileExportStyles(string $html): string
{
    $styles = <<<'CSS'
<style id="newsletter-mobile-export-css">
body.newsletter-mobile-export {
  width: 100%;
  max-width: none;
  margin: 0 auto;
  overflow-x: hidden;
  background: var(--color-white, #fff);
}

body.newsletter-mobile-export .main {
  width: 100%;
}

body.newsletter-mobile-export .newsletter-cover {
  min-block-size: 60vh;
  padding: calc(var(--size-9) + 80px) 0 var(--size-9);
  margin-left: 0;
  margin-right: 0;
  width: 100%;
}

body.newsletter-mobile-export .newsletter-logo {
  inline-size: 180px;
  margin-bottom: 4rem;
}

body.newsletter-mobile-export .newsletter-author-content {
  grid-template-columns: 1fr;
  gap: var(--size-6);
}

body.newsletter-mobile-export .newsletter-author-profile {
  flex-direction: row;
  align-items: center;
  text-align: left;
  gap: var(--size-4);
}

body.newsletter-mobile-export .author-avatar {
  width: 60px;
  height: 60px;
  margin-bottom: 0;
}

body.newsletter-mobile-export .timeline-container::before {
  left: 20px;
  width: 2px;
  transform: none;
}

body.newsletter-mobile-export .timeline-item {
  align-items: stretch;
  min-height: auto;
  margin: var(--size-6) 0;
  padding: 0 0 0 40px;
}

body.newsletter-mobile-export .timeline-item--left .timeline-item__container,
body.newsletter-mobile-export .timeline-item--right .timeline-item__container {
  display: grid;
  grid-template-columns: 72px minmax(0, 1fr);
  gap: var(--size-3);
  align-items: start;
  justify-content: flex-start;
  width: 100%;
  max-width: none;
  position: relative;
  text-align: left;
}

body.newsletter-mobile-export .timeline-item--left .timeline-content,
body.newsletter-mobile-export .timeline-item--right .timeline-content {
  order: initial;
  grid-column: 2;
  grid-row: 1;
  margin-left: 0;
  margin-right: 0;
  width: 100%;
  max-width: none;
  background: var(--color-white);
  border: 1px solid var(--color-dead-pixel-300);
  box-shadow: 0 2px 8px rgb(0 0 0 / 10%);
  padding: var(--size-6) var(--size-5) var(--size-5);
  border-radius: var(--border-radius-4);
  text-align: left;
}

body.newsletter-mobile-export .timeline-image {
  order: initial;
  grid-column: 1;
  grid-row: 1;
  position: static;
  transform: none;
  width: 72px;
  height: 72px;
  z-index: 2;
  margin: var(--size-2) 0 0;
}

body.newsletter-mobile-export .timeline-connector {
  left: -20px;
  top: 42px;
  transform: translateX(-50%);
}

body.newsletter-mobile-export .newsletter-static-map {
  min-height: 220px;
  border-radius: var(--border-radius-4);
  background:
    linear-gradient(135deg, rgb(251 198 46 / 20%), rgb(93 78 55 / 16%)),
    var(--color-dead-pixel-100, #f4f4f4);
  border: 1px solid var(--color-dead-pixel-300, #ddd);
  display: grid;
  place-items: center;
  gap: var(--size-3);
  text-align: center;
  color: var(--color-dead-pixel-1200, #333);
  padding: var(--size-5);
}

body.newsletter-mobile-export .newsletter-static-map__pin {
  width: 48px;
  height: 48px;
  margin: 0 auto;
}

body.newsletter-mobile-export .timeline-container {
  border-left: 2px solid #fbc62e !important;
  margin-left: 18px !important;
  padding-left: 28px !important;
  position: relative !important;
}

body.newsletter-mobile-export .timeline-container::before {
  content: none !important;
  display: none !important;
}

body.newsletter-mobile-export .timeline-item,
body.newsletter-mobile-export .timeline-item__container {
  display: block !important;
  width: 100% !important;
  max-width: none !important;
  min-height: 0 !important;
  margin: 0 0 24px !important;
  padding: 0 !important;
  position: relative !important;
  text-align: left !important;
}

body.newsletter-mobile-export .timeline-content {
  display: block !important;
  width: 100% !important;
  max-width: none !important;
  margin: 0 !important;
  position: static !important;
  text-align: left !important;
}

body.newsletter-mobile-export .timeline-image {
  display: block !important;
  position: static !important;
  transform: none !important;
  width: 96px !important;
  height: 96px !important;
  margin: 0 0 12px !important;
  overflow: hidden !important;
  border-radius: 999px !important;
}

body.newsletter-mobile-export .timeline-image img {
  display: block !important;
  width: 96px !important;
  height: 96px !important;
  max-width: 96px !important;
  object-fit: cover !important;
  border-radius: 999px !important;
}

body.newsletter-mobile-export .timeline-connector {
  display: none !important;
  width: 0 !important;
  height: 0 !important;
  overflow: hidden !important;
}

@media (max-width: 767px) {
  body.newsletter-mobile-export .content,
  body.newsletter-mobile-export .grid,
  body.newsletter-mobile-export .newsletter-grid,
  body.newsletter-mobile-export .calendar-grid,
  body.newsletter-mobile-export .events-list,
  body.newsletter-mobile-export .newsletter-months-grid {
    grid-template-columns: 1fr !important;
  }

  body.newsletter-mobile-export .grid-item,
  body.newsletter-mobile-export [data-span] {
    grid-column: 1 / -1 !important;
  }
}

@media (min-width: 768px) {
  body.newsletter-mobile-export .newsletter-cover {
    min-block-size: 70vh;
    padding: calc(var(--size-10) + 80px) 0 var(--size-10);
  }

  body.newsletter-mobile-export .newsletter-logo {
    inline-size: 250px;
    margin-bottom: 6rem;
  }

  body.newsletter-mobile-export .newsletter-author-content {
    grid-template-columns: 200px 1fr;
    gap: var(--size-8);
  }

  body.newsletter-mobile-export .newsletter-author-profile {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0;
  }

  body.newsletter-mobile-export .author-avatar {
    width: 80px;
    height: 80px;
    margin-bottom: var(--size-4);
  }

  body.newsletter-mobile-export .calendar-grid {
    grid-template-columns: repeat(5, 1fr);
  }

  body.newsletter-mobile-export .newsletter-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  }

  body.newsletter-mobile-export .events-list {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1040px) {
  body.newsletter-mobile-export .newsletter-months-grid {
    grid-template-columns: repeat(3, 1fr);
  }

  body.newsletter-mobile-export .events-list {
    grid-template-columns: repeat(3, 1fr);
  }
}
</style>
CSS;

    if (stripos($html, '</head>') !== false) {
        return str_ireplace('</head>', $styles . "\n</head>", $html);
    }

    return $styles . "\n" . $html;
}

function mmhNewsletterAbsoluteDocumentUrls(string $html): string
{
    $html = preg_replace_callback(
        '/\b(src|href)=([\'"])(\/(?!\/)[^\'"]*)\2/i',
        function (array $matches): string {
            return $matches[1] . '=' . $matches[2] . mmhAbsoluteUrl($matches[3]) . $matches[2];
        },
        $html,
    ) ?? $html;

    return preg_replace_callback(
        '/url\((["\']?)(\/(?!\/)[^)\'"]+)\1\)/i',
        function (array $matches): string {
            return 'url(' . $matches[1] . mmhAbsoluteUrl($matches[2]) . $matches[1] . ')';
        },
        $html,
    ) ?? $html;
}

function mmhNewsletterInlineLocalStylesheets(string $html): string
{
    return preg_replace_callback(
        '/<link\b([^>]*?)\bhref=(["\'])(\/assets\/css\/[^"\']+)\2([^>]*)>/i',
        function (array $matches): string {
            $path = parse_url($matches[3], PHP_URL_PATH);
            $file = mmhNewsletterPublicFilePath($path);

            if ($file === null) {
                return $matches[0];
            }

            $css = file_get_contents($file);

            if ($css === false) {
                return $matches[0];
            }

            $css = mmhNewsletterInlineCssImports($css, dirname($path));
            $css = mmhNewsletterInlineCssUrls($css, dirname($path));

            return '<style data-inline-source="' . esc($matches[3]) . '">' . "\n" . $css . "\n" . '</style>';
        },
        $html,
    ) ?? $html;
}

function mmhNewsletterInlineCssImports(string $css, string $basePath): string
{
    return preg_replace_callback(
        '/@import\s+url\((["\']?)([^)"\']+)\1\)\s*;/i',
        function (array $matches) use ($basePath): string {
            $url = trim($matches[2]);

            if (preg_match('!^(https?:)?//!i', $url) === 1) {
                return '';
            }

            $path = mmhNewsletterNormalizePublicPath($url, $basePath);
            $file = mmhNewsletterPublicFilePath($path);

            if ($file === null) {
                return $matches[0];
            }

            $imported = file_get_contents($file);

            if ($imported === false) {
                return $matches[0];
            }

            $imported = mmhNewsletterInlineCssImports($imported, dirname($path));

            return mmhNewsletterInlineCssUrls($imported, dirname($path));
        },
        $css,
    ) ?? $css;
}

function mmhNewsletterInlineCssUrls(string $css, string $basePath): string
{
    return preg_replace_callback(
        '/url\((["\']?)([^)"\']+)\1\)/i',
        function (array $matches) use ($basePath): string {
            $url = trim($matches[2]);

            if (
                $url === ''
                || str_starts_with($url, 'data:')
                || str_starts_with($url, '#')
                || preg_match('!^(https?:)?//!i', $url) === 1
            ) {
                return $matches[0];
            }

            $path = mmhNewsletterNormalizePublicPath($url, $basePath);
            $dataUri = mmhNewsletterDataUriForPublicPath($path);

            if ($dataUri === null) {
                return $matches[0];
            }

            return 'url(' . $matches[1] . $dataUri . $matches[1] . ')';
        },
        $css,
    ) ?? $css;
}

function mmhNewsletterInlineLocalAssets(string $html): string
{
    return preg_replace_callback(
        '/\b(src|href)=([\'"])(\/(?:assets|media)\/[^\'"]+)\2/i',
        function (array $matches): string {
            $dataUri = mmhNewsletterDataUriForPublicPath($matches[3]);

            if ($dataUri === null) {
                return $matches[0];
            }

            return $matches[1] . '=' . $matches[2] . $dataUri . $matches[2];
        },
        $html,
    ) ?? $html;
}

function mmhNewsletterInlineCriticalEmailStyles(string $html): string
{
    $classStyles = [
        'main' => 'display:block;width:100%;max-width:600px;margin:0 auto;overflow:hidden;background:#ffffff;color:#1f1f1f;font-family:Arial,Helvetica,sans-serif;line-height:1.45;',
        'newsletter-cover' => 'display:block;width:100%;max-width:600px;min-height:0;background:#5d4e37;color:#ffffff;text-align:center;padding:72px 20px;margin:0 auto 40px;box-sizing:border-box;',
        'newsletter-cover-content' => 'display:block;width:100%;max-width:560px;margin:0 auto;text-align:center;',
        'newsletter-logo' => 'display:block;width:180px;max-width:180px;height:auto;margin:0 auto 48px;',
        'newsletter-title' => 'display:block;color:#ffffff !important;font-size:34px;line-height:1.1;font-weight:700;margin:0 0 24px;text-align:center;text-shadow:0 2px 4px rgba(0,0,0,.35);',
        'newsletter-date' => 'display:block;color:#fbc62e !important;font-size:24px;line-height:1.2;font-weight:700;margin:0;text-align:center;text-shadow:0 1px 2px rgba(0,0,0,.35);',
        'content' => 'display:block;width:100%;max-width:600px;margin:0 auto 48px;padding:0 20px;box-sizing:border-box;',
        'grid' => 'display:block;width:100%;box-sizing:border-box;',
        'grid-item' => 'display:block;width:100%;box-sizing:border-box;margin:0 0 20px;',
        'newsletter-author-section' => 'display:block;background:#4a4a4a;color:#ffffff;border-radius:16px;padding:28px;margin:0 0 24px;box-sizing:border-box;',
        'newsletter-author-content' => 'display:block;width:100%;',
        'newsletter-author-profile' => 'display:block;width:100%;margin:0 0 20px;text-align:left;',
        'author-avatar' => 'display:block;width:64px;max-width:64px;height:64px;border-radius:50%;object-fit:cover;margin:0 16px 12px 0;',
        'author-name' => 'display:block;color:#ffffff;font-size:20px;line-height:1.25;font-weight:700;margin:0 0 4px;',
        'author-role' => 'display:block;color:#dddddd;font-size:13px;line-height:1.3;margin:0;',
        'newsletter-author-message' => 'display:block;color:#ffffff;font-size:16px;line-height:1.55;margin:0;',
        'c-newsletter-teaser' => 'display:block;width:100%;background:#4a4a4a;color:#ffffff;border-radius:16px;padding:28px;margin:0 0 24px;box-sizing:border-box;',
        'color-fg-light' => 'color:#ffffff !important;',
        'weekly-calendar' => 'display:block;width:100%;background:#ffffff;border:1px solid #dddddd;border-radius:16px;padding:24px;box-sizing:border-box;box-shadow:0 2px 8px rgba(0,0,0,.08);',
        'calendar-grid' => 'display:block;width:100%;',
        'calendar-day' => 'display:block;width:100%;margin:0 0 18px;',
        'calendar-day-label' => 'display:block;background:#c58b00;color:#ffffff;border-radius:8px;padding:10px 12px;font-weight:700;text-align:center;margin:0 0 10px;',
        'calendar-event' => 'display:block;background:#fff6d9;border-left:4px solid #d69a00;border-radius:8px;padding:12px;margin:0 0 10px;box-sizing:border-box;',
        'newsletter-months-grid' => 'display:block;width:100%;',
        'newsletter-month-item' => 'display:block;width:100%;margin:0 0 28px;',
        'events-list' => 'display:block;width:100%;list-style:none;margin:0;padding:0;',
        'eventsListItem' => 'display:block;width:100%;background:#ffffff;border:1px solid #dddddd;border-left:4px solid #d69a00;border-radius:12px;padding:18px;margin:0 0 16px;box-sizing:border-box;box-shadow:0 2px 8px rgba(0,0,0,.06);',
        'newsletter-grid' => 'display:block;width:100%;list-style:none;margin:0;padding:0;',
        'c-newsletterTeaserCard' => 'display:block;width:100%;background:#ffffff;border:1px solid #dddddd;border-radius:12px;overflow:hidden;margin:0 0 28px;padding:0 0 24px;box-sizing:border-box;',
        'c-projectUpdateTeaser-card' => 'display:block;width:100%;background:#ffffff;border:1px solid #dddddd;border-radius:12px;overflow:hidden;margin:0 0 20px;box-sizing:border-box;box-shadow:0 2px 8px rgba(0,0,0,.08);',
        'hero' => 'display:block;width:100%;height:auto;max-width:100%;',
        'statusheader' => 'display:block;margin:0 0 10px;',
        'status-badge' => 'display:inline-block;background:#fbc62e;color:#1f1f1f;border-radius:999px;padding:5px 10px;font-size:13px;font-weight:700;line-height:1.2;',
        'timeline-container' => 'display:block;width:100%;border-left:2px solid #fbc62e;margin:0 0 0 18px;padding:0 0 0 28px;box-sizing:border-box;position:relative;',
        'timeline-item' => 'display:block;width:100%;min-height:0;margin:0 0 24px;padding:0;box-sizing:border-box;position:relative;',
        'timeline-item__container' => 'display:block;width:100%;max-width:none;position:relative;text-align:left;',
        'timeline-content' => 'display:block;width:100%;max-width:none;background:#ffffff;border:1px solid #dddddd;border-radius:12px;padding:20px;box-sizing:border-box;box-shadow:0 2px 8px rgba(0,0,0,.08);text-align:left;margin:0;',
        'timeline-image' => 'display:block;width:96px;height:96px;margin:0 0 12px;position:static;overflow:hidden;border-radius:999px;',
        'timeline-connector' => 'display:none;width:0;height:0;overflow:hidden;',
        'timeline-date' => 'display:block;color:#c58b00;font-size:18px;line-height:1.25;font-weight:700;margin:0 0 8px;',
        'timeline-text' => 'display:block;color:#1f1f1f;font-size:16px;line-height:1.45;margin:0;',
        'newsletter-static-map' => 'display:block;min-height:180px;background:#f7f0dc;border:1px solid #dddddd;border-radius:12px;text-align:center;padding:32px 20px;box-sizing:border-box;color:#1f1f1f;',
        'newsletter-static-map__pin' => 'display:block;width:48px;max-width:48px;height:48px;margin:0 auto 12px;',
    ];

    foreach ($classStyles as $class => $style) {
        $html = mmhNewsletterAppendInlineStyleToClass($html, $class, $style);
    }

    $tagStyles = [
        'h1' => 'font-family:Arial,Helvetica,sans-serif;',
        'h2' => 'display:block;font-family:Arial,Helvetica,sans-serif;font-size:28px;line-height:1.2;font-weight:700;color:#1f1f1f;margin:0 0 20px;',
        'h3' => 'font-family:Arial,Helvetica,sans-serif;',
        'h4' => 'font-family:Arial,Helvetica,sans-serif;',
        'p' => 'font-family:Arial,Helvetica,sans-serif;font-size:16px;line-height:1.5;margin:0 0 12px;color:#1f1f1f;',
        'a' => 'color:inherit;',
        'img' => 'max-width:100%;height:auto;',
        'td' => 'font-family:Arial,Helvetica,sans-serif;',
        'table' => 'border-collapse:collapse;',
    ];

    foreach ($tagStyles as $tag => $style) {
        $html = mmhNewsletterAppendInlineStyleToTag($html, $tag, $style);
    }

    $html = mmhNewsletterAppendInlineStyleToClass(
        $html,
        'newsletter-title',
        'color:#ffffff !important;text-align:center;font-size:34px;line-height:1.1;font-weight:700;margin:0 0 24px;',
    );
    $html = mmhNewsletterAppendInlineStyleToClass(
        $html,
        'newsletter-date',
        'color:#fbc62e !important;text-align:center;font-size:24px;line-height:1.2;font-weight:700;margin:0;',
    );
    $html = mmhNewsletterAppendInlineStyleToClass(
        $html,
        'author-name',
        'color:#ffffff !important;',
    );
    $html = mmhNewsletterAppendInlineStyleToClass(
        $html,
        'author-role',
        'color:#dddddd !important;',
    );
    $html = mmhNewsletterAppendInlineStyleToClass(
        $html,
        'newsletter-author-message',
        'color:#ffffff !important;',
    );
    $html = mmhNewsletterInlineAuthorMessageText($html);
    $html = mmhNewsletterInlineClosingText($html);
    $html = mmhNewsletterNormalizeAuthorAvatar($html);

    return mmhNewsletterInlineTimelineImageTags($html);
}

function mmhNewsletterInlineAuthorMessageText(string $html): string
{
    return preg_replace_callback(
        '/(<div\b[^>]*\bclass=(["\'])(?=[^"\']*\bnewsletter-author-message\b)[^"\']*\2[^>]*>)(.*?)(<\/div>)/is',
        static function (array $matches): string {
            $content = preg_replace_callback(
                '/<p\b([^>]*)>/i',
                static fn (array $paragraph): string => '<p' . mmhNewsletterAppendStyleAttribute(
                    $paragraph[1],
                    'color:#ffffff !important;font-size:16px;line-height:1.55;',
                ) . '>',
                $matches[3],
            ) ?? $matches[3];

            return $matches[1] . $content . $matches[4];
        },
        $html,
    ) ?? $html;
}

function mmhNewsletterInlineClosingText(string $html): string
{
    return preg_replace_callback(
        '/(<div\b[^>]*\bclass=(["\'])(?=[^"\']*\bfont-body\b)(?=[^"\']*\bcolor-fg-light\b)[^"\']*\2[^>]*>)(.*?)(<\/div>)/is',
        static function (array $matches): string {
            $content = preg_replace_callback(
                '/<(p|a|h[1-6]|strong|em)\b([^>]*)>/i',
                static fn (array $element): string => '<' . $element[1] . mmhNewsletterAppendStyleAttribute(
                    $element[2],
                    'color:#ffffff !important;',
                ) . '>',
                $matches[3],
            ) ?? $matches[3];

            return $matches[1] . $content . $matches[4];
        },
        $html,
    ) ?? $html;
}

function mmhNewsletterNormalizeAuthorAvatar(string $html): string
{
    return preg_replace_callback(
        '/<img\b([^>]*\bclass=(["\'])(?=[^"\']*\bauthor-avatar\b)[^"\']*\2[^>]*)>/i',
        static function (array $matches): string {
            $attributes = preg_replace('/\s(?:width|height)=(["\']).*?\1/i', '', $matches[1]) ?? $matches[1];
            $attributes = mmhNewsletterAppendStyleAttribute(
                $attributes,
                'display:block;width:64px !important;max-width:64px !important;height:64px !important;object-fit:cover;border-radius:50% !important;',
            );

            return '<img' . $attributes . ' width="64" height="64">';
        },
        $html,
    ) ?? $html;
}

function mmhNewsletterInlineTimelineImageTags(string $html): string
{
    return preg_replace_callback(
        '/(<div\b[^>]*\bclass=(["\'])(?=[^"\']*\btimeline-image\b)[^"\']*\2[^>]*>\s*)<img\b([^>]*)>/i',
        static function (array $matches): string {
            $style = 'display:block;width:96px;height:96px;max-width:96px;object-fit:cover;border-radius:999px;';

            return $matches[1] . '<img' . mmhNewsletterAppendStyleAttribute($matches[3], $style) . '>';
        },
        $html,
    ) ?? $html;
}

function mmhNewsletterAppendInlineStyleToClass(string $html, string $class, string $style): string
{
    return preg_replace_callback(
        '/<([a-z][a-z0-9]*)\b([^>]*)>/i',
        static function (array $matches) use ($class, $style): string {
            if (preg_match('/\bclass=(["\'])(.*?)\1/i', $matches[2], $classMatch) !== 1) {
                return $matches[0];
            }

            $classes = preg_split('/\s+/', trim($classMatch[2])) ?: [];

            if (!in_array($class, $classes, true)) {
                return $matches[0];
            }

            return '<' . $matches[1] . mmhNewsletterAppendStyleAttribute($matches[2], $style) . '>';
        },
        $html,
    ) ?? $html;
}

function mmhNewsletterAppendInlineStyleToTag(string $html, string $tag, string $style): string
{
    $tag = preg_quote($tag, '/');

    return preg_replace_callback(
        '/<(' . $tag . ')\b([^>]*)>/i',
        static fn (array $matches): string => '<' . $matches[1] . mmhNewsletterAppendStyleAttribute($matches[2], $style) . '>',
        $html,
    ) ?? $html;
}

function mmhNewsletterAppendStyleAttribute(string $attributes, string $style): string
{
    if (preg_match('/\sstyle=(["\'])(.*?)\1/i', $attributes) === 1) {
        return preg_replace(
            '/\sstyle=(["\'])(.*?)\1/i',
            ' style="$2;' . $style . '"',
            $attributes,
            1,
        ) ?? $attributes;
    }

    return rtrim($attributes) . ' style="' . $style . '"';
}

function mmhNewsletterNormalizePublicPath(string $url, string $basePath): string
{
    $path = parse_url($url, PHP_URL_PATH) ?? '';

    if ($path === '') {
        return '';
    }

    if (str_starts_with($path, '/')) {
        return $path;
    }

    $parts = explode('/', trim($basePath . '/' . $path, '/'));
    $normalized = [];

    foreach ($parts as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }

        if ($part === '..') {
            array_pop($normalized);

            continue;
        }

        $normalized[] = $part;
    }

    return '/' . implode('/', $normalized);
}

function mmhNewsletterDataUriForPublicPath(string $path): ?string
{
    if ($path === '/assets/generated/mmh-logo-white.png') {
        return null;
    }

    if (str_starts_with($path, '/media/')) {
        return null;
    }

    $file = mmhNewsletterPublicFilePath($path);

    if ($file === null) {
        return null;
    }

    $size = filesize($file);
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if ($size !== false && $size > 750000 && $extension !== 'svg') {
        return null;
    }

    $contents = file_get_contents($file);

    if ($contents === false) {
        return null;
    }

    $mime = mime_content_type($file) ?: 'application/octet-stream';

    return 'data:' . $mime . ';base64,' . base64_encode($contents);
}

function mmhNewsletterPublicFilePath(?string $path): ?string
{
    if (!is_string($path) || $path === '' || str_starts_with($path, '//')) {
        return null;
    }

    $path = rawurldecode(parse_url($path, PHP_URL_PATH) ?? '');

    if ($path === '' || !str_starts_with($path, '/')) {
        return null;
    }

    $publicFile = mmhNewsletterFileInRoot(kirby()->root('index'), $path);

    if ($publicFile !== null) {
        return $publicFile;
    }

    if (str_starts_with($path, '/media/')) {
        $mediaFile = mmhNewsletterFileInRoot(kirby()->root('media'), substr($path, 6));

        if ($mediaFile !== null) {
            return $mediaFile;
        }

        $mediaFallback = mmhNewsletterMediaFallbackPath($path);

        if ($mediaFallback !== null) {
            return $mediaFallback;
        }
    }

    return null;
}

function mmhNewsletterFileInRoot(string $root, string $path): ?string
{
    $root = realpath(rtrim($root, DIRECTORY_SEPARATOR));

    if ($root === false) {
        return null;
    }

    $file = realpath($root . '/' . ltrim($path, '/'));

    if ($file === false || !is_file($file)) {
        return null;
    }

    if (!str_starts_with($file, $root . DIRECTORY_SEPARATOR)) {
        return null;
    }

    return $file;
}

function mmhNewsletterMediaFallbackPath(string $path): ?string
{
    $parts = explode('/', trim($path, '/'));

    if (count($parts) < 5 || $parts[0] !== 'media' || $parts[1] !== 'pages') {
        return null;
    }

    $filename = array_pop($parts);
    array_shift($parts);
    array_shift($parts);
    array_pop($parts);

    $mediaPattern = rtrim(kirby()->root('media'), DIRECTORY_SEPARATOR)
        . '/pages/'
        . implode('/', array_map('rawurlencode', $parts))
        . '/*/'
        . $filename;
    $mediaMatches = glob($mediaPattern);

    if (is_array($mediaMatches) && isset($mediaMatches[0]) && is_file($mediaMatches[0])) {
        return realpath($mediaMatches[0]) ?: null;
    }

    $contentMatches = mmhNewsletterContentFileMatches($parts, $filename);

    if (isset($contentMatches[0]) && is_file($contentMatches[0])) {
        return realpath($contentMatches[0]) ?: null;
    }

    return null;
}

function mmhNewsletterContentFileMatches(array $parts, string $filename, string $base = ''): array
{
    if ($parts === []) {
        $file = rtrim(kirby()->root('content'), DIRECTORY_SEPARATOR)
            . ($base !== '' ? '/' . $base : '')
            . '/'
            . $filename;

        return is_file($file) ? [$file] : [];
    }

    $part = array_shift($parts);
    $root = rtrim(kirby()->root('content'), DIRECTORY_SEPARATOR)
        . ($base !== '' ? '/' . $base : '');
    $matches = [];

    foreach ([$part, '*_' . $part] as $pattern) {
        foreach (glob($root . '/' . $pattern, GLOB_ONLYDIR) ?: [] as $dir) {
            $relative = ltrim(str_replace(rtrim(kirby()->root('content'), DIRECTORY_SEPARATOR), '', $dir), '/');
            $matches = array_merge($matches, mmhNewsletterContentFileMatches($parts, $filename, $relative));
        }
    }

    return $matches;
}

/**
 * Converts a relative site path to an absolute URL for external contexts
 * such as email clients.
 */
function mmhAbsoluteUrl(string $url): string
{
    if ($url === '') {
        return '';
    }

    if (preg_match('!^https?://!i', $url) === 1) {
        return $url;
    }

    $kirby = kirby();
    $baseCandidates = [
        $kirby->option('newsletter.email.baseUrl'),
        $kirby->option('url'),
        $kirby->request()->url()->base(),
        site()->url(),
        getenv('DDEV_PRIMARY_URL_WITHOUT_PORT') ?: null,
        getenv('DDEV_PRIMARY_URL') ?: null,
    ];

    foreach ($baseCandidates as $base) {
        $base = is_string($base) ? rtrim($base, '/') : '';

        if (preg_match('!^https?://!i', $base) === 1) {
            return $base . '/' . ltrim($url, '/');
        }
    }

    return url($url);
}
