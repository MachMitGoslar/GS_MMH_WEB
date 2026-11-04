<?php

use Kirby\Cms\Page;
use Mmh\Site\ProjectStatus;
use Mmh\Site\PublishedDate;
use PHPUnit\Framework\TestCase;

final class ProjectStatusTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/mmh-ghost-' . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/{,_changes/}*', GLOB_BRACE) ?: [] as $file) {
            is_file($file) && unlink($file);
        }
        @rmdir($this->dir . '/_changes');
        @rmdir($this->dir);
    }

    public function testAFolderWithOnlyTheUuidFileIsRemoved(): void
    {
        file_put_contents($this->dir . '/project.txt', "Uuid: abc123\n");
        mkdir($this->dir . '/_changes');

        ProjectStatus::removeGhost($this->dir);

        $this->assertDirectoryDoesNotExist($this->dir);
    }

    public function testAFolderWithRealContentStays(): void
    {
        file_put_contents($this->dir . '/project.txt', "Title: Ein Projekt\n\n----\n\nUuid: abc123\n");

        ProjectStatus::removeGhost($this->dir);

        $this->assertFileExists($this->dir . '/project.txt');
    }

    public function testAFolderWithFurtherFilesStays(): void
    {
        file_put_contents($this->dir . '/project.txt', "Uuid: abc123\n");
        file_put_contents($this->dir . '/bild.jpg', 'x');

        ProjectStatus::removeGhost($this->dir);

        $this->assertFileExists($this->dir . '/project.txt');
    }

    public function testAMissingFolderIsIgnored(): void
    {
        ProjectStatus::removeGhost($this->dir . '/gibt-es-nicht');

        $this->assertDirectoryExists($this->dir);
    }

    public function testOtherTemplatesKeepTheirPublishedDate(): void
    {
        $old = new Page(['slug' => 'a', 'template' => 'default', 'content' => ['title' => 'A']]);
        $new = new Page(['slug' => 'a', 'template' => 'default', 'content' => ['title' => 'A']]);

        PublishedDate::afterStatusChange($new, $old);

        $this->assertTrue($new->published()->isEmpty());
    }
}
