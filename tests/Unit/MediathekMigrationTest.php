<?php

use Kirby\Filesystem\Dir;
use Mmh\Site\MediathekMigration;
use PHPUnit\Framework\TestCase;

/**
 * The file-level migration into the Mediathek, against a small content tree
 * in a temp folder (never the real content, never a Kirby boot).
 */
final class MediathekMigrationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/mmh-migration-phpunit-' . uniqid();

        // the same photo three times: home (cover), notes entry (block), project
        $this->image('home/haus.jpg', 'HAUS', ['Uuid' => 'haushome', 'Focus' => '50% 50%']);
        $this->image('1_notes/eintrag/haus-kopie.jpg', 'HAUS', ['Uuid' => 'hausnote', 'Focus' => '50% 50%']);
        $this->image('2_projects/1_projekt/haus.jpg', 'HAUS', ['Uuid' => 'hausproj', 'Focus' => '20% 80%']);
        $this->page('home/home.txt', ['Title' => 'Start', 'Cover' => '- file://haushome']);
        $this->page('1_notes/eintrag/note.txt', [
            'Title' => 'Eintrag',
            'Layout' => '[{"columns":[{"blocks":[{"content":{"image":["file://hausnote"]},"type":"image"}]}]}]',
        ]);
        // the project also references the home copy once, so home stays canonical
        $this->page('2_projects/1_projekt/project.txt', ['Title' => 'Projekt', 'Cover' => '- file://hausproj']);
        $this->page('about/default.txt', ['Title' => 'Über', 'Social-image' => '- file://haushome']);

        // a single image on a page without cover (first image = implicit cover)
        $this->image('team/anna/anna.jpg', 'ANNA', ['Uuid' => 'anna']);
        $this->image('team/anna/z-zweites.jpg', 'ZWEI', ['Uuid' => 'zwei']);
        $this->page('team/anna/member.txt', ['Title' => 'Anna']);
        // … whose image is also on another page
        $this->image('3_informations/anna-kopie.jpg', 'ANNA', ['Uuid' => 'annainfo']);
        $this->page('3_informations/informations.txt', ['Title' => 'Infos', 'Cover' => '- file://annainfo']);

        // signage image, used by a slide
        $this->image('signage/slides/montag/bg.jpg', 'BG', ['Uuid' => 'bg1']);
        $this->page('signage/slides/montag/slide.txt', ['Title' => 'Montag', 'Bg-image' => '- file://bg1']);

        // form uploads are never touched
        $this->image('forms/kontakt/x/upload.jpg', 'HAUS', []);
        $this->page('forms/kontakt/form.txt', ['Title' => 'Kontakt']);
    }

    protected function tearDown(): void
    {
        Dir::remove($this->root);
    }

    public function testDryRunChangesNothing(): void
    {
        $before = $this->snapshot();

        $plan = (new MediathekMigration($this->root))->plan();

        $this->assertSame($before, $this->snapshot());
        $this->assertSame(2, $plan['stats']['groups']);
        $this->assertSame(3, $plan['stats']['deleted']);
    }

    public function testCreatesMediathekAndAlbums(): void
    {
        $this->migrate();

        $this->assertFileExists($this->root . '/mediathek/mediathek.txt');
        foreach (array_keys(MediathekMigration::ALBUMS) as $album) {
            $this->assertFileExists($this->root . "/mediathek/$album/mediathek-album.txt");
        }
    }

    public function testDuplicatesBecomeOneLibraryImage(): void
    {
        $this->migrate();

        // home copy has the most references (cover + social image) → stays
        $this->assertFileExists($this->root . '/mediathek/allgemein/haus.jpg');
        $this->assertFileDoesNotExist($this->root . '/home/haus.jpg');
        $this->assertFileDoesNotExist($this->root . '/1_notes/eintrag/haus-kopie.jpg');
        $this->assertFileDoesNotExist($this->root . '/1_notes/eintrag/haus-kopie.jpg.txt');
        $this->assertFileDoesNotExist($this->root . '/2_projects/1_projekt/haus.jpg');

        $meta = MediathekMigration::parse(file_get_contents($this->root . '/mediathek/allgemein/haus.jpg.txt'));
        $this->assertSame('haushome', $meta['uuid']);
        $this->assertSame('mediathek-image', $meta['template']);
        $this->assertSame(sha1('HAUS'), $meta['source_hash']);
        $this->assertSame('50% 50%', $meta['focus']);
    }

    public function testReferencesAreRewrittenInFieldsAndBlocks(): void
    {
        $this->migrate();

        $this->assertStringContainsString('"image":["file://haushome"]', $this->read('1_notes/eintrag/note.txt'));
        $this->assertStringContainsString('Cover: - file://haushome', $this->read('2_projects/1_projekt/project.txt'));
        $this->assertStringContainsString('file://haushome', $this->read('about/default.txt'));
    }

    public function testADifferentCropMovesToTheUsage(): void
    {
        $this->migrate();

        $project = MediathekMigration::parse($this->read('2_projects/1_projekt/project.txt'));
        $this->assertSame('20% 80%', $project['cover_focus']);

        $home = MediathekMigration::parse($this->read('home/home.txt'));
        $this->assertArrayNotHasKey('cover_focus', $home);
    }

    public function testImplicitCoverIsKept(): void
    {
        $this->migrate();

        // anna.jpg was the first image of the member page and is now a copy
        // of the informations image → the page gets that one as cover
        $member = MediathekMigration::parse($this->read('team/anna/member.txt'));
        $this->assertSame('- file://annainfo', $member['cover']);
        $this->assertFileExists($this->root . '/team/anna/z-zweites.jpg');
    }

    public function testSignageImagesMoveToTheirAlbum(): void
    {
        $this->migrate();

        $this->assertFileExists($this->root . '/mediathek/signage/bg.jpg');
        $this->assertFileDoesNotExist($this->root . '/signage/slides/montag/bg.jpg');
        $this->assertStringContainsString('file://bg1', $this->read('signage/slides/montag/slide.txt'));
        $this->assertArrayNotHasKey('cover', MediathekMigration::parse($this->read('signage/slides/montag/slide.txt')));
    }

    public function testFormUploadsStay(): void
    {
        $this->migrate();

        $this->assertFileExists($this->root . '/forms/kontakt/x/upload.jpg');
    }

    public function testReportListsTheSteps(): void
    {
        $migration = new MediathekMigration($this->root);
        $report = $migration->report($migration->plan());

        $this->assertStringContainsString('# Mediathek-Migration', $report);
        $this->assertStringContainsString('home/haus.jpg → mediathek/allgemein/haus.jpg (3 Kopien)', $report);
        $this->assertStringContainsString('2_projects/1_projekt/project.txt: cover_focus = 20% 80%', $report);
    }

    private function migrate(): void
    {
        $migration = new MediathekMigration($this->root);
        $migration->apply($migration->plan());
    }

    private function image(string $rel, string $bytes, array $meta): void
    {
        $this->put($rel, $bytes);
        if ($meta !== []) {
            $this->put($rel . '.txt', MediathekMigration::encode($meta));
        }
    }

    private function page(string $rel, array $fields): void
    {
        $this->put($rel, MediathekMigration::encode($fields));
    }

    private function put(string $rel, string $content): void
    {
        Dir::make(dirname($this->root . '/' . $rel));
        file_put_contents($this->root . '/' . $rel, $content);
    }

    private function read(string $rel): string
    {
        return (string) file_get_contents($this->root . '/' . $rel);
    }

    private function snapshot(): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            $files[substr($file->getPathname(), strlen($this->root))] = sha1_file($file->getPathname());
        }
        ksort($files);

        return $files;
    }
}
