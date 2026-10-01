<?php
/**
 * Responsive Bild: <picture> mit WebP-Source + Fallback im Originalformat.
 *
 * @var \Kirby\Cms\File|null $file  Bilddatei
 * @var string $role   hero | content | card | thumb (Standard: content)
 * @var string $sizes  sizes-Attribut (Standard: 100vw)
 * @var string $class  CSS-Klasse fürs <img>
 * @var string|null $alt  Alt-Text überschreiben ('' = dekorativ)
 * @var bool $lazy  lazy loading (Standard: true; false für Hero/LCP)
 * @var string|null $ratio  Zuschnitt, z. B. '2:1' (Standard: kein Zuschnitt)
 */

use Kirby\Toolkit\Html;

if (!isset($file) || !$file) {
    return;
}

$role = $role ?? 'content';
$sizes = $sizes ?? '100vw';
$class = $class ?? null;
$lazy = $lazy ?? true;
$ratio = $ratio ?? null;
$alt = $alt ?? $file->alt()->or($file->caption())->value();

$widths = [
    'hero' => [640, 1024, 1600, 1920],
    'content' => [400, 800, 1200],
    'card' => [320, 480, 640],
    'thumb' => [200, 400],
][$role] ?? [400, 800, 1200];

$focus = $file->focus()->isNotEmpty() ? $file->focus() : null;
$style = null;
if ($focus) {
    [$fx, $fy] = array_pad(explode(' ', trim($focus->value())), 2, '50%');
    $style = 'object-position: ' . $fx . ' ' . $fy;
}

// SVG/GIF nicht neu berechnen
if (in_array($file->extension(), ['svg', 'gif'], true)) {
    echo Html::tag('img', null, [
        'src' => $file->url(),
        'alt' => $alt,
        'class' => $class,
        'loading' => $lazy ? 'lazy' : null,
        'decoding' => 'async',
    ]);
    return;
}

$height = null;
if ($ratio && preg_match('/^(\d+):(\d+)$/', $ratio, $m)) {
    $height = fn (int $w) => (int) round($w * $m[2] / $m[1]);
}

$build = function (?string $format) use ($file, $widths, $height): array {
    $set = [];
    $last = null;
    foreach ($widths as $w) {
        if(is_object($w) && $w->exists() && $w <= 0) {
            continue; // ungültige Breite überspringen
        }
        if ($w > $file->width()) {
            continue; // nicht hochskalieren
        }
        $options = ['width' => $w, 'format' => $format];
        if ($height) {
            $options['height'] = $height($w);
            $options['crop'] = true;
        }
        $thumb = $file->thumb(array_filter($options, fn ($v) => $v !== null));
        $set[] = $thumb->url() . ' ' . $w . 'w';
        $last = $thumb;
    }
    if (!$set) { // Original kleiner als kleinste Breite
        $last = $file;
        $set[] = $file->url() . ' ' . $file->width() . 'w';
    }
    return [implode(', ', $set), $last];
};

[$webpSet] = $build('webp');
[$fallbackSet, $largest] = $build(null);
?>
<picture>
  <source type="image/webp" srcset="<?= $webpSet ?>" sizes="<?= $sizes ?>">
  <?= Html::tag('img', null, [
      'src' => $largest->url(),
      'srcset' => $fallbackSet,
      'sizes' => $sizes,
      'width' => $largest->width(),
      'height' => $largest->height(),
      'alt' => $alt,
      'class' => $class,
      'style' => $style,
      'loading' => $lazy ? 'lazy' : null,
      'fetchpriority' => $lazy ? null : 'high',
      'decoding' => 'async',
  ]) ?>
</picture>
