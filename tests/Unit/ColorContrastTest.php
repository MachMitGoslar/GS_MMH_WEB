<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ColorContrastTest extends TestCase
{
    #[DataProvider('invalidInput')]
    public function testRejectsAnythingThatIsNotAHexColor(?string $input): void
    {
        $this->assertNull(mmhColorContrast($input));
    }

    public static function invalidInput(): array
    {
        return [
            [null],
            [''],
            ['red'],
            ['#fff'],
            ['#12345g'],
            ['#ffffff;}body{display:none'],
            ['red;}'],
        ];
    }

    public function testNormalizesCaseAndWhitespace(): void
    {
        $this->assertSame('#1b4d89', mmhColorContrast("  #1B4D89 \n")['bg']);
    }

    public function testDarkColorGetsWhiteTextAndKeepsItsInk(): void
    {
        $result = mmhColorContrast('#1b4d89');

        $this->assertSame('#ffffff', $result['on']);
        $this->assertSame('#1b4d89', $result['ink'], 'dark colors already reach 3:1 on white');
    }

    public function testLightColorGetsDarkTextAndDarkenedInk(): void
    {
        $result = mmhColorContrast('#ffe14d');

        $this->assertSame('#6e6e6e', $result['on']);
        $this->assertNotSame('#ffe14d', $result['ink']);
        $this->assertGreaterThanOrEqual(3.0, self::contrastAgainstWhite($result['ink']));
    }

    public function testResultIsAlwaysSafeCssHex(): void
    {
        foreach (['#000000', '#ffffff', '#866811', '#ff00ff', '#00ff00'] as $hex) {
            $result = mmhColorContrast($hex);

            foreach (['bg', 'on', 'ink'] as $key) {
                $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $result[$key]);
            }
        }
    }

    private static function contrastAgainstWhite(string $hex): float
    {
        $channels = array_map(
            static function (string $pair): float {
                $v = hexdec($pair) / 255;

                return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
            },
            str_split(substr($hex, 1), 2),
        );
        $lum = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];

        return 1.05 / ($lum + 0.05);
    }
}
