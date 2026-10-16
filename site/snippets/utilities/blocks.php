<?php

/**
 * Blocks: renders the visible blocks, each in its anchor wrapper.
 *
 * Blocks hidden by their publish/end dates are skipped (isTimedContentVisible).
 *
 * A block's `theme` and `variant` fields (fields/blockTheme.yml, per-block variants)
 * become data-theme / data-variant on the wrapper: themes set surface tokens
 * (design-system/components/theme.css), variants are read by the block's snippet.
 *
 * @var \Kirby\Cms\Blocks $blocks
 */

foreach ($blocks as $block) {
    if (!$block->isVisible()) {
        continue;
    }

    // A card list shows white cards on the page; a theme stored on it (from an
    // earlier blueprint) must not turn every card into a themed surface.
    $themable = $block->type() !== 'card-list';
    $theme = $themable ? $block->content()->get('theme')->value() : null;
    $variant = $block->content()->get('variant')->value();
    $attrs = ($theme && $theme !== 'default' ? ' data-theme="' . esc($theme) . '"' : '')
        . ($variant && $variant !== 'auto' ? ' data-variant="' . esc($variant) . '"' : '');
    ?>
<div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>"<?= $attrs ?>>
    <?= $block ?>
</div>
    <?php
}
