<?php

namespace Mmh\Tests\Unit;

use Kirby\Cms\Block;
use Kirby\Cms\Layout;
use Kirby\Cms\Page;
use PHPUnit\Framework\TestCase;

/**
 * Pages, layouts and blocks answer `isVisible()` (publish/end dates), so
 * templates and snippets never check dates themselves. The rule itself is
 * tested in HelpersTest; this guards that the methods exist and delegate.
 */
class VisibilityMethodsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        kirby()->clone(require dirname(__DIR__, 2) . '/site/plugins/mmh-site/extensions.php');
    }

    protected function tearDown(): void
    {
        kirby()->impersonate(null);
    }

    public function testBlockIsVisibleFollowsItsDates(): void
    {
        $block = fn (array $content) => new Block(['type' => 'text', 'content' => $content]);

        $this->assertTrue($block(['text' => 'no dates'])->isVisible());
        $this->assertFalse($block(['publish_date' => '2099-01-01 10:00'])->isVisible());
        $this->assertFalse($block(['end_date' => '2000-01-01 10:00'])->isVisible());
    }

    public function testLayoutIsVisibleReadsTheAttributes(): void
    {
        $layout = fn (array $attrs) => new Layout(['id' => 'l', 'attrs' => $attrs, 'columns' => []]);

        $this->assertTrue($layout([])->isVisible());
        $this->assertFalse($layout(['publish_date' => '2099-01-01 10:00'])->isVisible());
    }

    public function testPageIsVisibleFollowsItsDates(): void
    {
        $page = fn (array $content) => new Page(['slug' => 'p', 'content' => $content]);

        $this->assertTrue($page([])->isVisible());
        $this->assertFalse($page(['end_date' => '2000-01-01 10:00'])->isVisible());
    }

    public function testEditorsSeeEverything(): void
    {
        kirby()->impersonate('kirby');

        $block = new Block(['type' => 'text', 'content' => ['publish_date' => '2099-01-01 10:00']]);
        $this->assertTrue($block->isVisible());
    }
}
