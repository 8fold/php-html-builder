<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Dom\HTMLDocument;
use Dom\XPath;

use Eightfold\HtmlBuilder\Forms\Select;

use Eightfold\HtmlBuilder\Element;

class SelectTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function modes(): iterable
    {
        foreach (['dropdown', 'multiple', 'radio', 'checkbox'] as $mode) {
            yield $mode => [$mode];
        }
    }

    /**
     * @return iterable<string, array{string, string|list<string>, list<string>}>
     */
    public static function selections(): iterable
    {
        foreach (['dropdown', 'multiple', 'radio', 'checkbox'] as $mode) {
            yield $mode . ': empty' => [$mode, [], []];
            yield $mode . ': scalar' => [$mode, 'b', ['b']];
            yield $mode . ': unmatched' => [$mode, ['missing'], []];
            yield $mode . ': numeric key' => [$mode, ['0'], ['0']];
            yield $mode . ': array' => [
                $mode,
                ['b', 'a'],
                in_array($mode, ['multiple', 'checkbox'], true) ? ['a', 'b'] : ['b']
            ];
            yield $mode . ': unmatched first value' => [
                $mode,
                ['missing', 'b'],
                in_array($mode, ['multiple', 'checkbox'], true) ? ['b'] : []
            ];
        }
    }

    /**
     * @param string|list<string> $selected
     * @param list<string> $expected
     */
    #[Test]
    #[DataProvider('selections')]
    public function selection_is_driven_by_mode_and_selected_values(
        string $mode,
        string|array $selected,
        array $expected
    ): void {
        $control = Select::create(
            'Categories',
            'categories',
            ['a' => 'A', 'b' => 'B', '0' => 'Zero'],
            $selected
        );
        $xpath = self::controlXpath($control->$mode()->inputProps('checked checked', 'multiple multiple'));
        $nodes = $xpath->query(
            '//*[local-name()="option" and @selected or local-name()="input" and @checked]'
        );
        parent::assertNotFalse($nodes);
        $actual = [];
        foreach ($nodes as $node) {
            $actual[] = $node->getAttribute('value');
        }
        parent::assertSame(
            $expected,
            $actual,
            $mode . ': inputProps must not override selection; single modes use only the first supplied value.'
        );
        parent::assertSame(
            $mode === 'multiple' ? 1 : 0,
            $xpath->query('//*[@multiple]')->length,
            $mode . ': only explicit multiple-dropdown mode may emit multiple.'
        );
    }

    #[Test]
    #[DataProvider('modes')]
    public function native_attributes_target_controls_and_preserve_generated_identity(string $mode): void
    {
        $control = Select::create('Categories', 'categories', ['a' => 'A', 'b' => 'B']);
        $isGroup = in_array($mode, ['radio', 'checkbox'], true);
        $xpath = self::controlXpath(
            $control->$mode()->id('custom')->inputProps(
                'id wrong',
                'name wrong',
                'type wrong',
                'value wrong',
                'required required',
                'disabled disabled',
                'aria-describedby category-help',
                ...($isGroup ? [] : ['size 4'])
            )->content(Element::p('Help')->props('id category-help'))
                ->wrapperProps('class wrapper')->labelProps('class caption')
        );
        $controls = $xpath->query('//*[local-name()="input" or local-name()="select"]');
        parent::assertNotFalse($controls);
        parent::assertCount($isGroup ? 2 : 1, $controls);
        foreach ($controls as $index => $control) {
            $id = $isGroup ? 'custom-option-' . $index : 'custom';
            parent::assertSame($id, $control->getAttribute('id'), $mode . ': generated ID must win.');
            parent::assertSame(
                $mode === 'checkbox' ? 'categories[]' : 'categories',
                $control->getAttribute('name'),
                $mode . ': generated submitted name must win.'
            );
            parent::assertTrue($control->hasAttribute('required'), $mode . ': required must reach the control.');
            parent::assertTrue($control->hasAttribute('disabled'), $mode . ': disabled must reach the control.');
            if (!$isGroup) {
                parent::assertSame('4', $control->getAttribute('size'), 'Dropdown size must reach the select.');
            }
            parent::assertSame('category-help', $control->getAttribute('aria-describedby'));
            parent::assertFalse($control->hasAttribute('aria-invalid'), 'Help alone must not imply an invalid value.');
            parent::assertSame(
                1,
                $xpath->query('//*[local-name()="label" and @for="' . $id . '"]')->length,
                $mode . ': each control must retain its associated label.'
            );
            if ($isGroup) {
                parent::assertSame($mode, $control->getAttribute('type'), 'Generated input type must win.');
                parent::assertSame(['a', 'b'][$index], $control->getAttribute('value'), 'Option value must win.');
            }
        }
        parent::assertSame(0, $xpath->query('//*[@class="wrapper" and (@required or @disabled or @size or @aria-describedby)]')->length);
        parent::assertSame(
            1,
            $xpath->query('//*[@class="wrapper" and local-name()="' . ($isGroup ? 'fieldset' : 'div') . '"]')->length
        );
        parent::assertSame(
            1,
            $xpath->query('//*[@class="caption" and local-name()="' . ($isGroup ? 'legend' : 'label') . '"]')->length
        );
    }

    #[Test]
    public function switching_from_multiple_to_dropdown_restores_single_selection(): void
    {
        $control = Select::create('Categories', 'categories', ['a' => 'A', 'b' => 'B'], ['b', 'a']);
        $xpath = self::controlXpath($control->multiple()->dropdown());
        parent::assertSame(0, $xpath->query('//*[@multiple]')->length, 'dropdown() must remove multiple mode.');
        parent::assertSame(1, $xpath->query('//*[local-name()="option" and @selected]')->length);
        parent::assertSame(
            'b',
            $xpath->evaluate('string(//*[local-name()="option" and @selected]/@value)'),
            'Returning to dropdown mode must select only the first supplied value.'
        );
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function submittedNames(): iterable
    {
        foreach (['categories', 'categories[]'] as $name) {
            foreach (self::modes() as [$mode]) {
                yield $mode . ': ' . $name => [
                    $mode,
                    $name,
                    $mode === 'checkbox' ? 'categories[]' : $name
                ];
            }
        }
    }

    #[Test]
    #[DataProvider('submittedNames')]
    public function submitted_names_preserve_existing_conventions(
        string $mode,
        string $name,
        string $expected
    ): void {
        $control = Select::create('Categories', $name, ['a' => 'A']);
        $xpath = self::controlXpath($control->$mode());
        parent::assertSame(
            $expected,
            $xpath->evaluate('string(//*[local-name()="input" or local-name()="select"]/@name)'),
            $mode . ': only checkbox groups append [], and never twice.'
        );
    }

    #[Test]
    #[DataProvider('modes')]
    public function group_disabling_and_required_attributes_follow_native_semantics(string $mode): void
    {
        $control = Select::create('Categories', 'categories', ['a' => 'A', 'b' => 'B']);
        $xpath = self::controlXpath(
            $control->$mode()->wrapperProps('disabled disabled')->inputProps('required required')
        );
        $isGroup = in_array($mode, ['radio', 'checkbox'], true);
        parent::assertSame($isGroup ? 1 : 0, $xpath->query('//*[local-name()="fieldset" and @disabled]')->length);
        parent::assertSame(
            $isGroup ? 2 : 1,
            $xpath->query('//*[local-name()="input" and @required or local-name()="select" and @required]')->length,
            'required is forwarded to each control, not interpreted as a group validation rule.'
        );
        parent::assertSame(
            0,
            $xpath->query('//*[local-name()="input" and @disabled or local-name()="select" and @disabled]')->length,
            'Group disabling belongs on the fieldset; member attributes must not be rewritten.'
        );
    }

    private static function controlXpath(Select $control): XPath
    {
        return new XPath(HTMLDocument::createFromString(
            '<!doctype html><html><head><title>Select test</title></head><body>'
                . $control . '</body></html>'
        ));
    }

    #[Test]
    public function ticket_36(): void // phpcs: ignore
    {
        $expected = <<<html
        <fieldset><legend>Toppings</legend><div><input id="toppings-option-0" name="toppings[]" type="checkbox" value="mushroom" aria-describedby="toppings-warning" aria-invalid="true"><label for="toppings-option-0">Mushroom</label></div><div><input id="toppings-option-1" name="toppings[]" type="checkbox" value="olive" aria-describedby="toppings-warning" aria-invalid="true"><label for="toppings-option-1">Olive</label></div><p id="toppings-warning">Choose at least one topping.</p></fieldset>
        html;

        $result = (string) Select::create(
            'Toppings',
            'toppings',
            ['mushroom' => 'Mushroom', 'olive' => 'Olive'],
            [],
            'Choose at least one topping.'
        )->checkbox();

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_be_multiple(): void // phpcs: ignore
    {
        $expected = <<<html
        <div><label for="select">Select</label><select id="select" name="select" aria-describedby="select-warning" aria-invalid="true" multiple><option value="value">display</option><option value="value2">display2</option></select><ul id="select-warning"><li>Invalid value</li></ul></div>
        html;

        $result = (string) Select::create(
            name: 'select',
            options: [
                'value'  => 'display',
                'value2' => 'display2'
            ],
            warningMessage: Element::ul(
                Element::li('Invalid value')
            ),
            label: 'Select'
        )->multiple();

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function warning_message_can_be_property_interface(): void // phpcs: ignore
    {
        $expected = <<<html
        <div><label for="select">Select</label><select id="select" name="select" aria-describedby="select-warning" aria-invalid="true"><option value="value">display</option><option value="value2">display2</option></select><ul id="select-warning"><li>Invalid value</li></ul></div>
        html;

        $result = (string) Select::create(
            label: 'Select',
            name: 'select',
            options: [
                'value'  => 'display',
                'value2' => 'display2'
            ],
            warningMessage: Element::ul(
                Element::li('Invalid value')
            )
        );

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function warning_message_can_be_string(): void // phpcs: ignore
    {
        $expected = <<<html
        <div><label for="select">Select</label><select id="select" name="select" aria-describedby="select-warning" aria-invalid="true"><option value="value">display</option><option value="value2">display2</option></select><p id="select-warning">Invalid value</p></div>
        html;

        $result = (string) Select::create(
            label: 'Select',
            name: 'select',
            options: [
                'value'  => 'display',
                'value2' => 'display2'
            ],
            warningMessage: 'Invalid value'
        );

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_add_props_to_label(): void // phpcs: ignore
    {
        $expected = <<<html
        <div><label is="select-dropdown" for="select">Select</label><select id="select" name="select"><option value="value">display</option><option value="value2">display2</option></select></div>
        html;

        $result = (string) Select::create(
            label: 'Select',
            name: 'select',
            options: [
                'value'  => 'display',
                'value2' => 'display2'
            ]
        )->labelProps('is select-dropdown');

        parent::assertSame($expected, $result);

        $expected = <<<html
        <fieldset><legend is="checkbox">Select</legend><div><input id="select-option-0" name="select[]" type="checkbox" value="value"><label for="select-option-0">display</label></div><div><input id="select-option-1" name="select[]" type="checkbox" value="value2"><label for="select-option-1">display2</label></div></fieldset>
        html;

        $result = (string) Select::create(
            label: 'Select',
            name: 'select',
            options: [
                'value'  => 'display',
                'value2' => 'display2'
            ]
        )->checkbox()->labelProps('is checkbox');

        parent::assertSame($expected, $result);

        $expected = <<<html
        <fieldset><legend is="radio">Select</legend><div><input id="select-option-0" name="select" type="radio" value="value"><label for="select-option-0">display</label></div><div><input id="select-option-1" name="select" type="radio" value="value2"><label for="select-option-1">display2</label></div></fieldset>
        html;

        $result = (string) Select::create(
            label: 'Select',
            name: 'select',
            options: [
                'value'  => 'display',
                'value2' => 'display2'
            ]
        )->radio()->labelProps('is radio');

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_be_checkboxes(): void // phpcs: ignore
    {
        $expected = <<<html
        <fieldset><legend>Select your option</legend><div><input id="select-option-0" name="select[]" type="checkbox" value="value" checked><label for="select-option-0">display</label></div><div><input id="select-option-1" name="select[]" type="checkbox" value="value2" checked><label for="select-option-1">display2</label></div></fieldset>
        html;

        $result = (string) Select::create(
            label: 'Select your option',
            name: 'select',
            options: [
                'value'  => 'display',
                'value2' => 'display2'
            ],
            selected: ['value', 'value2']
        )->checkbox();

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_be_radio_buttons(): void // phpcs: ignore
    {
        $expected = <<<html
        <fieldset><legend>Select your option</legend><div><input id="select-option-0" name="select" type="radio" value="value" checked><label for="select-option-0">display</label></div></fieldset>
        html;

        $result = (string) Select::create(
            'Select your option',
            'select',
            [
                'value' => 'display'
            ],
            'value'
        )->radio();

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function dropdown_mode_is_explicit(): void // phpcs: ignore
    {
        $expected = <<<html
        <div><label for="select">Select your option</label><select id="select" name="select"><option value="value">display</option></select></div>
        html;

        $result = (string) Select::create(
            'Select your option',
            'select',
            [
                'value' => 'display'
            ]
        )->radio()->dropdown();

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function checkbox_mode_is_explicit(): void // phpcs: ignore
    {
        $expected = <<<html
        <fieldset><legend>Select your option</legend><div><input id="select-option-0" name="select[]" type="checkbox" value="value" checked><label for="select-option-0">display</label></div></fieldset>
        html;

        $result = (string) Select::create(
            'Select your option',
            'select',
            [
                'value' => 'display'
            ],
            'value'
        )->radio()->checkbox();

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_add_properties_to_wrapper(): void // phpcs: ignore
    {
        $expected = <<<html
        <div id="some-id" class="some-token"><label for="select">Select your option</label><select id="select" name="select"><option value="value">display</option></select></div>
        html;

        $result = (string) Select::create(
            'Select your option',
            'select',
            [
                'value' => 'display'
            ]
        )->wrapperProps('id some-id', 'class some-token');

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_preselect_option(): void // phpcs:ignore
    {
        $expected = <<<html
        <div><label for="select">Select your option</label><select id="select" name="select"><option value="value">display</option><option value="value2" selected>display2</option></select></div>
        html;

        $result = (string) Select::create(
            'Select your option',
            'select',
            [
                'value'  => 'display',
                'value2' => 'display2'
            ],
            'value2'
        );

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function is_expected_base(): void // phpcs:ignore
    {
        $expected = <<<html
        <div><label for="select">Select your option</label><select id="select" name="select"><option value="value">display</option></select></div>
        html;

        $result = (string) Select::create(
            'Select your option',
            'select',
            [
                'value' => 'display'
            ]
        );

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function error_is_selected_value_always_string(): void // phpcs:ignore
    {
        $expected = <<<html
        <div><label for="select">Select your option</label><select id="select" name="select"><option value="0">display</option></select></div>
        html;

        // Even with strict types, number-based keys become integers
        $result = (string) Select::create(
            'Select your option',
            'select',
            [
                '0' => 'display'
            ]
        );

        parent::assertSame($expected, $result);
    }
}
