<?php
/**
 * Download Block
 *
 * Available blocks
 * - $block->file()   -> the file
 * - $block->title()  -> main title
 * - $block->text()   -> description in black
 */
/** @var \Kirby\Cms\Block $block */
?>

<?php if ($file = $block->file()->toFile()): ?>
    <!-- Ganze Box klickbar -->
    <a href="<?= $file->url() ?>" download class="c-downloadBlock flex items-start gap-4 p-4 border rounded-md bg-white shadow-sm no-underline">

        <!-- Icon links -->
        <div class="c-downloadBlock__icon flex-shrink-0 mt-1">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="w-8 h-8">
                <path d="M12 12V19M12 19L9.75 16.6667M12 19L14.25 16.6667M6.6 17.8333C4.61178 17.8333 3 16.1917 3 14.1667C3 12.498 4.09438 11.0897 5.59198 10.6457C5.65562 10.6268 5.7 10.5675 5.7 10.5C5.7 7.46243 8.11766 5 11.1 5C14.0823 5 16.5 7.46243 16.5 10.5C16.5 10.5582 16.5536 10.6014 16.6094 10.5887C16.8638 10.5306 17.1284 10.5 17.4 10.5C19.3882 10.5 21 12.1416 21 14.1667C21 16.1917 19.3882 17.8333 17.4 17.8333" stroke="#000" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </div>

        <!-- Textbereich -->
        <div class="c-downloadBlock__content">
            <?php if ($block->title()->isNotEmpty()): ?>
                <p class="c-downloadBlock__title"><?= $block->title() ?></p>
            <?php endif; ?>
            <?php if ($block->text()->isNotEmpty()): ?>
                <p class="c-downloadBlock__description"><?= $block->text() ?></p>
            <?php endif; ?>
        </div>

    </a>
<?php endif; ?>
