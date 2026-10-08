<?php

use PHPUnit\Framework\TestCase;

final class ProjectPageTest extends TestCase
{
    private static function project(array $content = [], array $steps = []): ProjectPage
    {
        $children = [];
        foreach ($steps as $i => $step) {
            $children[] = [
                'slug' => 'step-' . $i,
                'num' => $step['listed'] ?? true ? $i + 1 : null,
                'content' => array_diff_key($step, ['listed' => 1]),
            ];
        }

        return new ProjectPage([
            'slug' => 'projekt',
            'content' => $content,
            'children' => $children,
        ]);
    }

    public function testProjectColorIsDerivedFromTheField(): void
    {
        $project = self::project(['project_color' => '#1b4d89']);

        $this->assertSame('#1b4d89', $project->projectColor()['bg']);
        $this->assertSame('#ffffff', $project->projectColor()['on']);
    }

    public function testProjectColorIsNullWhenUnsetOrInvalid(): void
    {
        $this->assertNull(self::project()->projectColor());
        $this->assertNull(self::project(['project_color' => 'red;}'])->projectColor());
    }

    public function testAccentsFollowTheToggleOnlyWhenAColorIsSet(): void
    {
        $this->assertFalse(self::project()->useProjectAccents(), 'no color, no accents');
        $this->assertTrue(self::project(['project_color' => '#1b4d89'])->useProjectAccents(), 'toggle unset = on');
        $this->assertTrue(self::project(['project_color' => '#1b4d89', 'project_color_accents' => 'true'])->useProjectAccents());
        $this->assertFalse(self::project(['project_color' => '#1b4d89', 'project_color_accents' => 'false'])->useProjectAccents());
    }

    public function testExplicitStatusWins(): void
    {
        $project = self::project(
            ['project_status' => '  aktiv '],
            [['project_start_date' => '2026-01-01', 'project_status_to' => 'abgeschlossen']],
        );

        $this->assertSame('aktiv', $project->effectiveProjectStatus());
    }

    public function testStatusFallsBackToTheLatestListedStepWithAStatus(): void
    {
        $project = self::project([], [
            ['project_start_date' => '2026-01-01', 'project_status_to' => 'in Planung'],
            ['project_start_date' => '2026-03-01', 'project_status_to' => 'aktiv'],
            ['project_start_date' => '2026-05-01', 'project_status_to' => ''],
            ['project_start_date' => '2026-07-01', 'project_status_to' => 'abgeschlossen', 'listed' => false],
        ]);

        $this->assertSame('aktiv', $project->effectiveProjectStatus());
    }

    public function testStatusIsEmptyWithoutAnyInformation(): void
    {
        $this->assertSame('', self::project()->effectiveProjectStatus());
    }

    public function testStepsAreSortedNewestFirstUsingDateAndTime(): void
    {
        $project = self::project([], [
            ['project_start_date' => '2026-01-01'],
            ['project_start_date' => '2026-03-01', 'project_start_time' => '09:00'],
            ['project_start_date' => '2026-03-01', 'project_start_time' => '17:30'],
            ['project_start_date' => ''],
        ]);

        $this->assertSame(
            ['step-2', 'step-1', 'step-0', 'step-3'],
            $project->project_steps()->pluck('slug'),
        );
    }

    public function testLatestStepDate(): void
    {
        $project = self::project([], [
            ['project_start_date' => '2026-01-01'],
            ['project_start_date' => '2026-03-01', 'project_start_time' => '17:30'],
        ]);

        $this->assertSame(strtotime('2026-03-01 17:30'), $project->latestStepDate());
        $this->assertSame(0, self::project()->latestStepDate());
    }

    public function testTopicAndTags(): void
    {
        $project = self::project(['topic' => ' klima ', 'tags' => 'Garten, Wald,Stadt']);

        $this->assertSame('klima', $project->topicSlug());
        $this->assertSame(['Garten', 'Wald', 'Stadt'], $project->tagList());
        $this->assertSame('', self::project()->topicSlug());
    }
}
