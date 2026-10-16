<?php

namespace Mmh\Tests\Unit;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Cms\Page;
use Kirby\Form\Form;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The layout field of each page type extends the shared `fields/layout`
 * (rows, size) and only overrides what differs. Guards the merge behavior:
 * lists (layouts, fieldsets) replace the base list, settings merge by key.
 */
class BlueprintLayoutTest extends TestCase
{
    private static App $app;

    public static function setUpBeforeClass(): void
    {
        self::$app = kirby()->clone([
            'roots' => ['blueprints' => dirname(__DIR__, 2) . '/site/blueprints'],
        ]);
    }

    private function layoutField(string $template, string $field): array
    {
        $page = new Page(['slug' => 'x', 'template' => $template, 'kirby' => self::$app]);

        return $page->blueprint()->field($field);
    }

    /** Block types of a fieldsets definition, groups flattened. */
    private static function blockTypes(array $fieldsets): array
    {
        $types = [];
        foreach ($fieldsets as $key => $fieldset) {
            if (is_array($fieldset) && ($fieldset['type'] ?? null) === 'group') {
                $types = [...$types, ...self::blockTypes($fieldset['fieldsets'])];
            } else {
                $types[] = is_int($key) ? $fieldset : $key;
            }
        }

        return $types;
    }

    public function testPagesShareTheBaseBlockList(): void
    {
        $default = self::blockTypes($this->layoutField('default', 'layout')['fieldsets']);

        $this->assertSame($default, self::blockTypes($this->layoutField('project', 'text')['fieldsets']));
        $this->assertSame($default, self::blockTypes($this->layoutField('theme', 'text')['fieldsets']));
    }

    public function testBlocksFieldsShareTheLayoutBlockList(): void
    {
        $expected = self::blockTypes($this->layoutField('default', 'layout')['fieldsets']);

        foreach (['note', 'contact'] as $template) {
            $field = $this->layoutField($template, 'text');

            $this->assertSame('blocks', $field['type'], $template);
            $this->assertSame($expected, self::blockTypes($field['fieldsets']), $template);
        }
    }

    public static function pageTypes(): array
    {
        // template, field, label, rows, block types, settings
        return [
            'default' => ['default', 'layout', 'Inhalt', 6, 20, ['publish_date', 'end_date']],
            'about' => ['about', 'layout', 'Inhalt', 3, 2, []],
            'project' => ['project', 'text', 'Text', 6, 20, ['highlight']],
            'theme' => ['theme', 'text', 'Einleitung', 6, 20, []],
        ];
    }

    #[DataProvider('pageTypes')]
    public function testLayoutFieldExtendsSharedBase(string $template, string $field, string $label, int $rows, int $blocks, array $settings): void
    {
        $layout = $this->layoutField($template, $field);

        $this->assertSame('layout', $layout['type']);
        $this->assertSame('huge', $layout['size']);
        $this->assertSame($label, $layout['label']);
        $this->assertCount($rows, $layout['layouts']);
        $this->assertCount($blocks, self::blockTypes($layout['fieldsets']));
        $this->assertSame($settings, array_keys($layout['settings']['fields'] ?? []));
    }

    public function testSettingsReferenceSharedFields(): void
    {
        // settings fields are shorthand references, resolved by Kirby when the form is built
        $page = new Page(['slug' => 'x', 'template' => 'default', 'kirby' => self::$app]);
        $settings = Form::for($page)->field('layout')->settings()->fields();

        $this->assertSame('date', $settings['publish_date']['type']);
        $this->assertSame('date', $settings['end_date']['type']);

        $highlight = Blueprint::load('fields/layoutHighlight');
        $this->assertSame('select', $highlight['type']);
        $this->assertSame('none', $highlight['default']);
        $this->assertSame('fields/layoutHighlight', $this->layoutField('project', 'text')['settings']['fields']['highlight']);
    }

    public function testCardBlockOffersThemeAndVariantInEveryBlockList(): void
    {
        foreach ([['default', 'layout'], ['note', 'text']] as [$template, $field]) {
            $fieldsets = $this->layoutField($template, $field)['fieldsets'];
            $card = null;
            foreach ($fieldsets as $group) {
                if (isset($group['fieldsets']['card'])) {
                    $card = $group['fieldsets']['card'];
                }
            }

            $this->assertNotNull($card, $template);
            $this->assertSame('fields/blockTheme', $card['fields']['theme'], $template);
            $this->assertSame('fields/cardVariant', $card['fields']['variant'], $template);
        }
    }

    public function testSharedFieldAtomsOfferTheSameVocabulary(): void
    {
        $theme = Blueprint::load('fields/blockTheme');
        $this->assertSame(['default', 'soft', 'brand', 'dark', 'project'], array_keys($theme['options']));
        $this->assertSame('default', $theme['default']);

        $variant = Blueprint::load('fields/cardVariant');
        $this->assertSame(['auto', 'vertical'], array_keys($variant['options']));
    }
}
