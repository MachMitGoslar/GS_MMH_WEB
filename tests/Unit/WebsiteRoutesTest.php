<?php

use Mmh\Site\MergedProjects;
use PHPUnit\Framework\TestCase;

final class WebsiteRoutesTest extends TestCase
{
    private static function routes(): array
    {
        return require dirname(__DIR__, 2) . '/site/plugins/mmh-site/routes.php';
    }

    public function testTheWebsiteRoutesAreRegisteredByThePlugin(): void
    {
        $patterns = array_map(
            fn (array $route) => json_encode($route['pattern']),
            self::routes(),
        );

        $this->assertEqualsCanonicalizing([
            '"version.json"',
            '"\/stipendien"',
            '["projects\/(:any)","project-archive\/(:any)"]',
            '"events\/(:num).ics"',
            '"events\/(:num)"',
            '"events.json"',
            '"newsletter.xml"',
            '"catalog"',
            '"\/not-allowed"',
        ], $patterns);

        foreach (self::routes() as $route) {
            $this->assertIsCallable($route['action']);
        }
    }

    public function testMergedProjectsPointToTheirNewPlace(): void
    {
        $this->assertSame(
            'projects/burgerkunst-mein-buntes-goslar#kapitel-mein-buntes-goslar',
            MergedProjects::target('mein-buntes-goslar'),
        );
        $this->assertNull(MergedProjects::target('machmitwald'), 'independent projects are not redirected');
        $this->assertNull(MergedProjects::target(''));
    }

    public function testEveryRedirectTargetIsAProjectPathWithAChapterAnchor(): void
    {
        foreach (MergedProjects::TARGETS as $old => $target) {
            $this->assertMatchesRegularExpression('!^projects/[a-z0-9-]+#kapitel-[a-z0-9-]+$!', $target, $old);
        }
    }
}
