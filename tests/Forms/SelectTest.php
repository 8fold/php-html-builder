<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Forms\Select;

use Eightfold\HtmlBuilder\Element;

class SelectTest extends TestCase
{
    #[Test]
    public function ticket_36(): void // phpcs: ignore
    {
        $expected = <<<html
        <fieldset><legend>Toppings</legend><div><input id="toppings-mushroom" name="toppings[]" type="checkbox" value="mushroom" aria-describedby="toppings-warning" aria-invalid="true"><label for="toppings-mushroom">Mushroom</label></div><div><input id="toppings-olive" name="toppings[]" type="checkbox" value="olive" aria-describedby="toppings-warning" aria-invalid="true"><label for="toppings-olive">Olive</label></div><p id="toppings-warning">Choose at least one topping.</p></fieldset>
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
        <fieldset><legend is="checkbox">Select</legend><div><input id="select-value" name="select[]" type="checkbox" value="value"><label for="select-value">display</label></div><div><input id="select-value2" name="select[]" type="checkbox" value="value2"><label for="select-value2">display2</label></div></fieldset>
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
        <fieldset><legend is="radio">Select</legend><div><input id="select-value" name="select" type="radio" value="value"><label for="select-value">display</label></div><div><input id="select-value2" name="select" type="radio" value="value2"><label for="select-value2">display2</label></div></fieldset>
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
        <fieldset><legend>Select your option</legend><div><input id="select-value" name="select[]" type="checkbox" value="value" checked><label for="select-value">display</label></div><div><input id="select-value2" name="select[]" type="checkbox" value="value2" checked><label for="select-value2">display2</label></div></fieldset>
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
        <fieldset><legend>Select your option</legend><div><input id="select-value" name="select" type="radio" value="value" checked><label for="select-value">display</label></div></fieldset>
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
        <fieldset><legend>Select your option</legend><div><input id="select-value" name="select[]" type="checkbox" value="value" checked><label for="select-value">display</label></div></fieldset>
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
