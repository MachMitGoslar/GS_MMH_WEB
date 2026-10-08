<?php

use PHPUnit\Framework\TestCase;

final class HoroscopeTest extends TestCase
{
    private const LABELS = [
        'freude' => 'Freude',
        'glueck' => 'Glück',
        'energie' => 'Energie',
        'gesundheit' => 'Gesundheit',
        'motivation' => 'Motivation',
    ];

    public function testSignsAreSortedByNumericOrder(): void
    {
        $signs = [
            ['sign' => 'taurus', 'order' => 2],
            ['sign' => 'pisces', 'order' => '12'],
            ['sign' => 'aries', 'order' => 1],
        ];

        $this->assertSame(['aries', 'taurus', 'pisces'], array_column(mmhHoroscopeSortSigns($signs), 'sign'));
    }

    public function testSignsKeepTheApiOrderWhenOrderHoldsTheAttributes(): void
    {
        $attributes = ['freude' => 5, 'glueck' => 3];
        $signs = [
            ['sign' => 'aries', 'order' => $attributes],
            ['sign' => 'taurus', 'order' => $attributes],
            ['sign' => 'gemini', 'order' => $attributes],
        ];

        $this->assertSame(['aries', 'taurus', 'gemini'], array_column(mmhHoroscopeSortSigns($signs), 'sign'));
    }

    public function testAttributesComeFromTheAttributesField(): void
    {
        $sign = ['attributes' => ['glueck' => 7, 'freude' => 2]];

        $this->assertSame(
            ['freude' => ['label' => 'Freude', 'value' => 2], 'glueck' => ['label' => 'Glück', 'value' => 7]],
            mmhHoroscopeAttributes($sign, self::LABELS),
            'result follows the label order, not the API order',
        );
    }

    public function testAttributesFallBackToTheOrderField(): void
    {
        $sign = ['order' => ['energie' => 4, 'motivation' => '6']];

        $this->assertSame(
            ['energie' => ['label' => 'Energie', 'value' => 4], 'motivation' => ['label' => 'Motivation', 'value' => 6]],
            mmhHoroscopeAttributes($sign, self::LABELS),
        );
    }

    public function testAttributeValuesAreClampedToTheScale(): void
    {
        $sign = ['attributes' => ['freude' => 99, 'glueck' => -4, 'energie' => 0, 'gesundheit' => 8]];
        $values = array_column(mmhHoroscopeAttributes($sign, self::LABELS), 'value');

        $this->assertSame([8, 0, 0, 8], $values);
        $this->assertSame([3], array_column(mmhHoroscopeAttributes(['attributes' => ['freude' => 9]], self::LABELS, 3), 'value'));
    }

    public function testMissingOrUnknownAttributesAreLeftOut(): void
    {
        $this->assertSame([], mmhHoroscopeAttributes([], self::LABELS));
        $this->assertSame([], mmhHoroscopeAttributes(['order' => 5], self::LABELS), 'numeric order is a sort key, not attributes');
        $this->assertSame([], mmhHoroscopeAttributes(['attributes' => ['unbekannt' => 5]], self::LABELS));
    }
}
