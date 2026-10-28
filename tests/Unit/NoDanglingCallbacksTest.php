<?php

use PHPUnit\Framework\TestCase;

/**
 * Code that moved from global functions into classes must not keep calling the
 * old names through string callbacks (array_map('mmhFoo', ...)): PHP only
 * notices when the line runs.
 */
final class NoDanglingCallbacksTest extends TestCase
{
    public function testClassesDoNotUseStringCallbacksToGlobalMmhFunctions(): void
    {
        $root = dirname(__DIR__, 2) . '/site/plugins';
        $files = glob($root . '/mmh-site/src/{,*/}*.php', GLOB_BRACE);

        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            preg_match_all("/'(mmh[A-Z][A-Za-z0-9]+)'/", file_get_contents($file), $matches);

            foreach ($matches[1] as $name) {
                $this->assertTrue(function_exists($name), basename($file) . ": '$name' is not a function");
            }
        }
    }
}
