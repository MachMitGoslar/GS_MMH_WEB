<?php

/**
 * Avatar: a person's image, or their initial when there is none.
 *
 * Either pass a member page (`person`) or free data (`name` + optional `image`).
 * Renders only the image or the placeholder; the wrapping element, its size
 * and shape stay with the caller.
 *
 * @var \Kirby\Cms\Page|null $person Member page (name, cover)
 * @var string|null $name Display name (free data, or overrides the page's name)
 * @var \Kirby\Cms\File|null $image Image for free data
 * @var int $size Square box of the generated image in pixels
 * @var string|null $loading Value of the loading attribute (omitted when null)
 */

$person = $person ?? null;
$size = $size ?? 160;
$loading = $loading ?? null;

if ($person !== null) {
    $name ??= $person->name()->isNotEmpty() ? $person->name()->value() : $person->title()->value();
    $cover = $person->cover();
    $image ??= $cover instanceof \Kirby\Cms\File ? $cover : (is_object($cover) && method_exists($cover, 'toFile') ? $cover->toFile() : null);
}

$name = $name ?? '';
$image = $image ?? null;
$initial = mb_strtoupper(mb_substr($name, 0, 1));
?>
<?php if ($image) : ?>
    <?php $avatar = mmhAvatarImage($image, $size); ?>
  <img src="<?= $avatar['url'] ?>" data-fit="<?= $avatar['fit'] ?>" alt="<?= esc($name) ?>"<?= $loading ? ' loading="' . esc($loading) . '"' : '' ?>>
<?php else : ?>
  <span class="c-avatar-placeholder" aria-hidden="true"><span class="c-avatar-initial"><?= esc($initial) ?></span></span>
<?php endif ?>
