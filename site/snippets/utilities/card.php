<?php

/**
 * Card: the one base layout of every card on the site (project, theme, update,
 * newsletter, note, team member, page ...).
 *
 *   media   image (or placeholder), optional overlay on its lower edge
 *   body    meta line, title, subtitle, text, free body, actions
 *
 * Every part is optional. Callers only fill the parts and pick a variant;
 * markup and styling live here and in card.css. Do not build a new card
 * layout elsewhere: extend this one (parameter, data-variant) instead.
 *
 * @var string|null $variant Card kind, set as data-variant (project, theme, update, newsletter, note, person, page, entry)
 * @var string $orientation vertical (default) or horizontal (image left once the card is wide enough)
 * @var string $size regular (default) or featured
 * @var string|null $class Extra classes on the card (also hooks of the newsletter e-mail styles)
 * @var string $tag Wrapper element (default: li)
 * @var array $attrs Further attributes of the card, e.g. ['data-span' => '1/4']
 * @var \Kirby\Cms\File|null $image Cover
 * @var bool $placeholder Show the brand placeholder when there is no image (default: true)
 * @var array $imageOptions Extra options for utilities/image (role, ratio, sizes, alt)
 * @var callable|null $media Replaces the image with other markup (e.g. an avatar)
 * @var string|null $imageHref Makes the image a link
 * @var string|null $imageLabel aria-label of the image link
 * @var callable|null $overlay Markup on the image's lower or upper edge (badge, avatar stack)
 * @var callable|null $meta Line above the title (badge, date, tags)
 * @var string|null $title Title (HTML)
 * @var string|null $titleHref Makes the title a link
 * @var string|null $subtitle Subtitle (HTML)
 * @var string|null $text Running text (HTML)
 * @var callable|null $body Further markup below the text
 * @var array|null $action Call to action: ['label' => string, 'href' => string]
 * @var callable|null $actions Several actions (buttons) instead of $action
 */

$variant ??= null;
$orientation ??= 'vertical';
$size ??= 'regular';
$class ??= null;
$tag ??= 'li';
$attrs ??= [];
$image ??= null;
$placeholder ??= true;
$imageOptions ??= [];
$media ??= null;
$imageHref ??= null;
$imageLabel ??= null;
$overlay ??= null;
$meta ??= null;
$title ??= null;
$titleHref ??= null;
$subtitle ??= null;
$text ??= null;
$body ??= null;
$action ??= null;
$actions ??= null;

$classes = trim('c-card ' . ($class ?? ''));
$attrHtml = '';
foreach ($attrs as $name => $value) {
    $attrHtml .= ' ' . esc($name, 'attr') . '="' . esc($value) . '"';
}
$hasMedia = $media || $image || $placeholder;
$imageTag = $imageHref ? 'a' : 'div';
$titleClass = $size === 'featured' ? 'font-titleXL' : 'font-headline';
?>
<<?= $tag ?> class="<?= esc($classes) ?>"<?= $variant ? ' data-variant="' . esc($variant) . '"' : '' ?> data-orientation="<?= esc($orientation) ?>" data-size="<?= esc($size) ?>"<?= $attrHtml ?>>
  <div class="c-card__inner">
    <?php if ($hasMedia) : ?>
      <div class="c-card__media">
        <<?= $imageTag ?> class="c-card__image"<?= $imageHref ? ' href="' . esc($imageHref) . '"' : '' ?><?= $imageHref && $imageLabel ? ' aria-label="' . esc($imageLabel) . '"' : '' ?>>
          <?php if ($media) :
                $media();
          elseif ($image) :
              snippet('utilities/image', $imageOptions + [
                  'file' => $image,
                  'role' => 'card',
                  'sizes' => '(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw',
                  'class' => 'hero',
              ]);
          else :
              snippet('utilities/imagePlaceholder');
          endif ?>
        </<?= $imageTag ?>>
        <?php if ($overlay) : ?>
          <div class="c-card__overlay"><?php $overlay() ?></div>
        <?php endif ?>
      </div>
    <?php endif ?>
    <div class="c-card__body">
      <?php if ($meta) :
            $meta();
      endif ?>
      <?php if ($title) : ?>
        <h3 class="c-card__title <?= $titleClass ?> font-line-height-narrow"><?php if ($titleHref) :
            ?><a href="<?= esc($titleHref) ?>"><?= $title ?></a><?php
                                 else :
                                        ?><?= $title ?><?php
                                 endif ?></h3>
      <?php endif ?>
      <?php if ($subtitle) : ?>
        <p class="c-card__subtitle font-footnote"><?= $subtitle ?></p>
      <?php endif ?>
      <?php if ($text) : ?>
        <div class="c-card__text font-footnote"><?= $text ?></div>
      <?php endif ?>
      <?php if ($body) :
            $body();
      endif ?>
      <?php if ($actions) : ?>
        <div class="c-card__actions"><?php $actions() ?></div>
      <?php elseif ($action) : ?>
        <div class="c-card__actions">
          <?php snippet('utilities/button', ['label' => $action['label'], 'href' => $action['href'], 'variant' => 'secondary']) ?>
        </div>
      <?php endif ?>
    </div>
  </div>
</<?= $tag ?>>
