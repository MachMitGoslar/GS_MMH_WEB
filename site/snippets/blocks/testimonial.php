<?php

/** @var \Kirby\Cms\Block $block */

if ($block->quote()->isEmpty()) {
    return;
}

$member = $block->source()->value() === 'member' ? $block->member()->toPage() : null;
$name = $member ? ($member->name()->or($member->title())->value()) : $block->name()->value();
$role = $member
    ? $member->role()->value()
    : implode(', ', array_filter([$block->jobPosition()->value(), $block->company()->value()]));
$color = in_array($block->color()->value(), ['project', 'gold'], true) ? $block->color()->value() : 'default';
$hasPerson = $name !== '' || $role !== '';
$avatarData = $member ? ['person' => $member] : ['name' => $name, 'image' => $block->image()->toFile()];
?>
<figure class="c-testimonial" data-color="<?= esc($color) ?>">
  <blockquote class="c-testimonial__quote">
    <?= $block->quote() ?>
  </blockquote>
  <?php if ($hasPerson) : ?>
    <figcaption class="c-testimonial__caption">
      <?php if ($name !== '') : ?>
        <span class="c-testimonial__avatar">
          <?php snippet('utilities/avatar', $avatarData + ['size' => 112]) ?>
        </span>
      <?php endif ?>
      <span class="c-testimonial__person">
        <?php if ($name !== '') : ?>
          <span class="c-testimonial__name"><?= esc($name) ?></span>
        <?php endif ?>
        <?php if ($role !== '') : ?>
          <span class="c-testimonial__role"><?= esc($role) ?></span>
        <?php endif ?>
      </span>
    </figcaption>
  <?php endif ?>
</figure>
