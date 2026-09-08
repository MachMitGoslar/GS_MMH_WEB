<?php

/**
 * Icon Snippet
 *
 * Single source for all line icons on the site. The set follows the Feather
 * geometry the event and equipment icons were already drawn in: a 24x24 grid,
 * no fill, `currentColor` as stroke, round caps and joins. Because the stroke
 * inherits the text colour, an icon needs no variant per background.
 *
 * Use this instead of an emoji: emoji are rendered by the operating system, so
 * they differ per platform in style, colour and metrics and cannot follow the
 * design tokens.
 *
 * @var string $name  Icon key, see $icons below
 * @var int|null $size  Edge length in pixels, defaults to 20
 * @var string|null $class  Additional class names
 * @var string|null $label  Accessible name; omit for purely decorative icons
 */

$size ??= 20;
$class = trim('gs-c-icon ' . ($class ?? ''));
$label ??= null;

$icons = [
    // Communication
    'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"></rect><polyline points="22 6 12 13 2 6"></polyline>',
    'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path>',
    'globe' => '<circle cx="12" cy="12" r="9"></circle><line x1="3" y1="12" x2="21" y2="12"></line><path d="M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18z"></path>',
    'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>',
    'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line>',

    // Places and time
    'map-pin' => '<path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>',
    'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
    'clock' => '<circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline>',
    'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>',

    // Editorial
    'book-open' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>',
    'newspaper' => '<path d="M4 5h13a1 1 0 0 1 1 1v13a2 2 0 0 0 2 2H5a1 1 0 0 1-1-1z"></path><path d="M18 9h2a1 1 0 0 1 1 1v9a2 2 0 0 1-2 2"></path><line x1="7" y1="9" x2="15" y2="9"></line><line x1="7" y1="13" x2="15" y2="13"></line><line x1="7" y1="17" x2="12" y2="17"></line>',
    'zap' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>',
    'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="3"></circle>',

    // Status
    'check' => '<polyline points="20 6 9 17 4 12"></polyline>',
    'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>',
    'x' => '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>',
    'x-circle' => '<circle cx="12" cy="12" r="9"></circle><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line>',
    'alert-triangle' => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
    'info' => '<circle cx="12" cy="12" r="9"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>',
    'alert' => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
    'lightbulb' => '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.8.8 1.3 1.5 1.5 2.5"></path><path d="M9 18h6"></path><path d="M10 22h4"></path>',

    // Navigation
    'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>',
    'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline>',

    // People
    'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
    'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
    'accessibility' => '<circle cx="12" cy="4" r="2"></circle><path d="M19 13v-2a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v2"></path><circle cx="8" cy="19" r="3"></circle><path d="M15 19h4l-2-6h-4"></path>',
    'smile' => '<circle cx="12" cy="12" r="9"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line>',

    // Media and equipment
    'rotate-cw' => '<polyline points="21 2 21 8 15 8"></polyline><path d="M21 13a9 9 0 1 1-3-7.7L21 8"></path>',
    'camera' => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle>',
    'monitor' => '<rect x="2" y="3" width="20" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line>',
    'video' => '<polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2"></rect>',
    'mic' => '<path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line>',
    'wifi' => '<path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line>',
    'whiteboard' => '<rect x="3" y="3" width="18" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line><line x1="7" y1="8" x2="17" y2="8"></line>',
    'flipchart' => '<path d="M4 4h16v12H4z"></path><line x1="8" y1="16" x2="8" y2="22"></line><line x1="16" y1="16" x2="16" y2="22"></line><line x1="7" y1="8" x2="17" y2="8"></line><line x1="7" y1="11" x2="13" y2="11"></line>',
    'printer' => '<polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect>',
    'coffee' => '<path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line>',
    'ac' => '<rect x="2" y="4" width="20" height="8" rx="2"></rect><path d="M6 12v4"></path><path d="M10 12v6"></path><path d="M14 12v4"></path><path d="M18 12v6"></path>',
    'parking' => '<rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M9 17V7h4a3 3 0 0 1 0 6H9"></path>',

    // Misc
    'ticket' => '<path d="M3 9V7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 6v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-6z"></path><line x1="13" y1="5" x2="13" y2="19"></line>',
    'tag' => '<path d="M20.59 13.41 12 22l-9-9V3h10l7.59 7.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line>',
    'edit' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z"></path>',
    'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>',
    'repeat' => '<polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path>',
];

$body = $icons[$name] ?? $icons['info'];

$attrs = [
    'class="' . esc($class, 'attr') . '"',
    'data-icon="' . esc($name, 'attr') . '"',
    'width="' . (int) $size . '"',
    'height="' . (int) $size . '"',
    'viewBox="0 0 24 24"',
    'fill="none"',
    'stroke="currentColor"',
    'stroke-width="1.75"',
    'stroke-linecap="round"',
    'stroke-linejoin="round"',
    'focusable="false"',
];

if ($label !== null) {
    $attrs[] = 'role="img"';
    $attrs[] = 'aria-label="' . esc($label, 'attr') . '"';
} else {
    $attrs[] = 'aria-hidden="true"';
}

echo '<svg ' . implode(' ', $attrs) . '>' . $body . '</svg>';
