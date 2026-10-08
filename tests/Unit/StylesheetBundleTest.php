<?php

use PHPUnit\Framework\TestCase;

final class StylesheetBundleTest extends TestCase
{
    private static function bundle(): string
    {
        return mmhInlineStylesheet(__DIR__ . '/../fixtures/css/index.css');
    }

    public function testRemoteImportsAreHoistedAndDeduplicated(): void
    {
        $bundle = self::bundle();

        $this->assertStringStartsWith("@import url('https://fonts.example/a.css');", $bundle);
        $this->assertSame(1, substr_count($bundle, 'fonts.example'));
    }

    public function testLocalImportsAreFlattened(): void
    {
        $bundle = self::bundle();

        $this->assertStringNotContainsString('./a.css', $bundle);
        $this->assertStringNotContainsString('./b.css', $bundle);
        $this->assertStringContainsString('.a {', $bundle);
        $this->assertStringContainsString('.b {', $bundle);
        $this->assertStringContainsString('body {', $bundle);
    }

    public function testEveryFileIsInlinedOnceEvenWithImportCycles(): void
    {
        $bundle = self::bundle();

        $this->assertSame(1, substr_count($bundle, '.a {'));
        $this->assertSame(1, substr_count($bundle, '.b {'));
    }

    public function testImportedRulesComeBeforeTheImportingFile(): void
    {
        $bundle = self::bundle();

        $this->assertLessThan(strpos($bundle, '.a {'), strpos($bundle, '.b {'), 'b is imported by a, so it comes first');
        $this->assertLessThan(strpos($bundle, 'body {'), strpos($bundle, '.a {'));
    }

    public function testStylesheetVersionIsTheNewestMtimeOfTheCssTree(): void
    {
        $version = mmhStylesheetVersion();

        $this->assertGreaterThan(1_600_000_000, $version);
        $this->assertLessThanOrEqual(time(), $version);
    }
}
