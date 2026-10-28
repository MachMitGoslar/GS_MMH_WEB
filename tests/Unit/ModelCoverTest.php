<?php

use Kirby\Cms\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * cover() of the page models: the file chosen in the `cover` field, otherwise
 * the first image of the page. MemberPage differs on purpose, see below.
 */
final class ModelCoverTest extends TestCase
{
    private static function page(string $class, array $content, array $files = ['bild.jpg', 'anderes.jpg']): Kirby\Cms\Page
    {
        return new $class([
            'slug' => 'seite',
            'content' => $content,
            'files' => array_map(fn (string $name) => ['filename' => $name], $files),
        ]);
    }

    public static function models(): array
    {
        return [
            'default' => [DefaultPage::class],
            'home' => [HomePage::class],
            'note' => [NotePage::class],
            'project' => [ProjectPage::class],
            'theme' => [ThemePage::class],
            'member' => [MemberPage::class],
        ];
    }

    #[DataProvider('models')]
    public function testTheChosenFileWins(string $class): void
    {
        $cover = self::page($class, ['cover' => 'anderes.jpg'])->cover();

        $this->assertInstanceOf(File::class, $cover);
        $this->assertSame('anderes.jpg', $cover->filename());
    }

    #[DataProvider('models')]
    public function testNothingWithoutFilesOrCover(string $class): void
    {
        $this->assertNull(self::page($class, [], [])->cover());
    }

    #[DataProvider('models')]
    public function testMissingCoverFieldFallsBackToTheFirstImage(string $class): void
    {
        $cover = self::page($class, ['title' => 'ohne Feld'])->cover();

        $this->assertInstanceOf(File::class, $cover);
        $this->assertSame('bild.jpg', $cover->filename());
    }

    public static function modelsWithFallback(): array
    {
        $models = self::models();
        unset($models['member']);

        return $models;
    }

    #[DataProvider('modelsWithFallback')]
    public function testEmptyCoverFieldFallsBackToTheFirstImage(string $class): void
    {
        $this->assertSame('bild.jpg', self::page($class, ['cover' => ''])->cover()?->filename());
    }

    public function testMemberWithAnEmptyCoverFieldGetsNoFallbackImage(): void
    {
        // Current behaviour (kept): once the field exists, an empty value means "no cover".
        $this->assertNull(self::page(MemberPage::class, ['cover' => ''])->cover());
    }
}
