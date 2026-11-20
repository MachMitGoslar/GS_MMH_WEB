<?php

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Mmh\Site\Mediathek;
use PHPUnit\Framework\TestCase;

/**
 * The Mediathek guards: duplicate uploads, usage lookup by UUID and the
 * delete guard. Runs against a small content tree on disk (hashes need
 * real bytes).
 */
final class MediathekTest extends TestCase
{
    private ?App $original = null;

    private string $root;

    protected function setUp(): void
    {
        $this->original = App::instance();
        $this->root = sys_get_temp_dir() . '/mmh-mediathek-phpunit-' . uniqid();
        $content = $this->root . '/content';

        $this->write($content . '/mediathek/mediathek.txt', "Title: Mediathek\n");
        $this->write($content . '/mediathek/eingang/mediathek-album.txt', "Title: Eingang\n");
        $this->write($content . '/mediathek/raeume/mediathek-album.txt', "Title: Räume\n");
        $this->write($content . '/mediathek/raeume/foyer.jpg', 'FOYER-BYTES');
        $this->write($content . '/mediathek/raeume/foyer.jpg.txt', "Alt: Das Foyer\n\n----\n\nUuid: foyer1\n\n----\n\nTemplate: mediathek-image\n");
        $this->write($content . '/mediathek/raeume/gross.jpg', 'RESIZED-BYTES');
        $this->write($content . '/mediathek/raeume/gross.jpg.txt', "Uuid: gross1\n\n----\n\nSource-hash: " . sha1('ORIGINAL-BYTES') . "\n\n----\n\nTemplate: mediathek-image\n");
        $this->write($content . '/1_home/home.txt', "Title: Start\n\n----\n\nCover: - file://foyer1\n");
        $this->write($content . '/2_about/default.txt', "Title: Über uns\n\n----\n\nLayout: [{\"content\":{\"image\":[\"file://foyer1\"]}}]\n");
        $this->write($content . '/3_projekt/default.txt', "Title: Projekt\n");
        $this->write($content . '/forms/form/upload.txt', "Title: Upload\n");
        $this->write($content . '/site.txt', "Title: MMH\n\n----\n\nSocial-image: - file://gross1\n");

        new App([
            'roots' => [
                'index' => $this->root,
                'base' => $this->root,
                'content' => $content,
                'media' => $this->root . '/media',
                'cache' => $this->root . '/cache',
            ],
            'urls' => ['index' => 'https://mmh.test'],
        ]);

        Mediathek::flush();
    }

    protected function tearDown(): void
    {
        Mediathek::flush();
        Dir::remove($this->root);
        // Other tests rely on the app of tests/bootstrap.php
        App::instance($this->original);
    }

    public function testImagesOfAllAlbums(): void
    {
        $this->assertSame(['foyer.jpg', 'gross.jpg'], Mediathek::images()->pluck('filename'));
    }

    public function testRecognisesLibraryFiles(): void
    {
        $this->assertTrue(Mediathek::isInLibrary($this->foyer()));
        $this->assertFalse(Mediathek::isInLibrary($this->newFile('projekt', 'x.jpg')));
    }

    public function testHashUsesTheStoredSourceHashFirst(): void
    {
        $this->assertSame(sha1('FOYER-BYTES'), Mediathek::hashOf($this->foyer()));
        $this->assertSame(sha1('ORIGINAL-BYTES'), Mediathek::hashOf(kirby()->file('mediathek/raeume/gross.jpg')));
    }

    public function testDuplicateUploadIsRejectedWithAHint(): void
    {
        $this->expectException(DuplicateException::class);
        $this->expectExceptionMessage('Räume / Das Foyer');

        Mediathek::beforeCreate($this->newFile('projekt', 'kopie.jpg'), $this->upload('FOYER-BYTES'));
    }

    public function testResizedOriginalIsRecognisedByItsSourceHash(): void
    {
        $this->expectException(DuplicateException::class);

        Mediathek::beforeCreate($this->newFile('mediathek/eingang', 'gross-neu.jpg'), $this->upload('ORIGINAL-BYTES'));
    }

    public function testNewImageIsAccepted(): void
    {
        Mediathek::beforeCreate($this->newFile('mediathek/eingang', 'neu.jpg'), $this->upload('NEW-BYTES'));

        $this->addToAssertionCount(1);
    }

    public function testFormUploadsAndNonImagesAreNotChecked(): void
    {
        Mediathek::beforeCreate($this->newFile('forms/form', 'foyer.jpg'), $this->upload('FOYER-BYTES'));
        Mediathek::beforeCreate($this->newFile('projekt', 'foyer.pdf'), $this->upload('FOYER-BYTES', 'document'));

        $this->addToAssertionCount(1);
    }

    public function testUsageFindsPagesByUuidInFieldsAndBlocks(): void
    {
        $this->assertSame(['home', 'about'], Mediathek::usage($this->foyer())->pluck('id'));
        $this->assertSame('Verwendet an 2 Stellen: Start, Über uns', Mediathek::usageInfo($this->foyer()));
    }

    public function testUsageBySiteSettings(): void
    {
        $gross = kirby()->file('mediathek/raeume/gross.jpg');

        $this->assertSame(0, Mediathek::usage($gross)->count());
        $this->assertTrue(Mediathek::usedBySite($gross));
        $this->assertSame('Verwendet in: Website-Einstellungen', Mediathek::usageInfo($gross));
    }

    public function testImageInUseCannotBeDeleted(): void
    {
        $this->expectException(PermissionException::class);
        $this->expectExceptionMessage('Start, Über uns');

        Mediathek::guardDelete($this->foyer());
    }

    public function testUnusedImageCanBeDeleted(): void
    {
        $this->write($this->root . '/content/mediathek/raeume/frei.jpg', 'FREE');
        $this->write($this->root . '/content/mediathek/raeume/frei.jpg.txt', "Uuid: frei1\n");

        Mediathek::guardDelete(kirby()->file('mediathek/raeume/frei.jpg'));
        $this->assertSame('Noch nicht verwendet.', Mediathek::usageInfo(kirby()->file('mediathek/raeume/frei.jpg')));
    }

    private function foyer(): File
    {
        return kirby()->file('mediathek/raeume/foyer.jpg');
    }

    private function newFile(string $parent, string $filename): File
    {
        return new File(['filename' => $filename, 'parent' => kirby()->page($parent)]);
    }

    private function upload(string $bytes, string $type = 'image'): object
    {
        return new class ($bytes, $type) {
            public function __construct(private string $bytes, private string $type)
            {
            }

            public function sha1(): string
            {
                return sha1($this->bytes);
            }

            public function type(): string
            {
                return $this->type;
            }
        };
    }

    private function write(string $path, string $content): void
    {
        Dir::make(dirname($path));
        file_put_contents($path, $content);
    }
}
