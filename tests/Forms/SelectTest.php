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
    private static function collapsedHtml(string $html): string
    {
        return preg_replace('/>\s+</', '><', $html) ?? '';
    }

    #[Test]
    public function selection_is_driven_by_mode(): void
    {
        $html = <<<html
        <div>
            <label for="categories">Categories</label>
            <select id="categories" name="categories">
                <option value="a">A</option>
                <option value="b">B</option>
                <option value="0">Zero</option>
            </select>
        </div>
        html;

        $expected = self::collapsedHtml($html);

        $control = Select::create(
            'Categories',
            'categories',
            ['a' => 'A', 'b' => 'B', '0' => 'Zero']
        );

        $result = (string) $control;

        parent::assertSame($expected, $result);

        $html = <<<html
        <fieldset>
            <legend>Categories</legend>
            <div>
                <input id="categories-option-0" name="categories" type="radio" value="a">
                <label for="categories-option-0">A</label>
            </div>
            <div>
                <input id="categories-option-1" name="categories" type="radio" value="b">
                <label for="categories-option-1">B</label>
            </div>
            <div>
                <input id="categories-option-2" name="categories" type="radio" value="0">
                <label for="categories-option-2">Zero</label>
            </div>
        </fieldset>
        html;

        $expected = self::collapsedHtml($html);

        $result = (string) $control->radio();

        parent::assertSame($expected, $result);

        $html = <<<html
        <fieldset>
            <legend>Categories</legend>
            <div>
                <input id="categories-option-0" name="categories[]" type="checkbox" value="a">
                <label for="categories-option-0">A</label>
            </div>
            <div>
                <input id="categories-option-1" name="categories[]" type="checkbox" value="b">
                <label for="categories-option-1">B</label>
            </div>
            <div>
                <input id="categories-option-2" name="categories[]" type="checkbox" value="0">
                <label for="categories-option-2">Zero</label>
            </div>
        </fieldset>
        html;

        $expected = self::collapsedHtml($html);

        $result = (string) $control->checkbox();

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function ticket_36(): void // phpcs: ignore
    {
        // phpcs:disable
        $html = <<<html
        <fieldset>
            <legend>Toppings</legend>
            <div>
                <input id="toppings-option-0" name="toppings[]" type="checkbox" value="mushroom" aria-describedby="toppings-warning" aria-invalid="true">
                <label for="toppings-option-0">Mushroom</label>
            </div>
            <div>
                <input id="toppings-option-1" name="toppings[]" type="checkbox" value="olive" aria-describedby="toppings-warning" aria-invalid="true">
                <label for="toppings-option-1">Olive</label>
            </div>
            <p id="toppings-warning">Choose at least one topping.</p></fieldset>
        html;
        // phpcs:enable

        $expected = self::collapsedHtml($html);

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
        // phpcs:disable
        $html = <<<html
        <div>
            <label for="select">Select</label>
            <select id="select" name="select" aria-describedby="select-warning" aria-invalid="true" multiple>
                <option value="value">display</option>
                <option value="value2">display2</option>
            </select>
            <ul id="select-warning">
                <li>Invalid value</li>
            </ul>
        </div>
        html;
        // phpcs:enable

        $expected = self::collapsedHtml($html);

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
        $html = <<<html
        <div>
            <label for="select">Select</label>
            <select id="select" name="select" aria-describedby="select-warning" aria-invalid="true">
                <option value="value">display</option>
                <option value="value2">display2</option>
            </select>
            <ul id="select-warning">
                <li>Invalid value</li>
            </ul>
        </div>
        html;

        $expected = self::collapsedHtml($html);

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
        $html = <<<html
        <div>
            <label for="select">Select</label>
            <select id="select" name="select" aria-describedby="select-warning" aria-invalid="true">
                <option value="value">display</option>
                <option value="value2">display2</option>
            </select>
            <p id="select-warning">Invalid value</p>
        </div>
        html;

        $expected = self::collapsedHtml($html);

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
        $html = <<<html
        <div>
            <label is="select-dropdown" for="select">Select</label>
            <select id="select" name="select">
                <option value="value">display</option>
                <option value="value2">display2</option>
            </select>
        </div>
        html;

        $expected = self::collapsedHtml($html);

        $result = (string) Select::create(
            label: 'Select',
            name: 'select',
            options: [
                'value'  => 'display',
                'value2' => 'display2'
            ]
        )->labelProps('is select-dropdown');

        parent::assertSame($expected, $result);

        $html = <<<html
        <fieldset>
            <legend is="checkbox">Select</legend>
            <div>
                <input id="select-option-0" name="select[]" type="checkbox" value="value">
                <label for="select-option-0">display</label>
            </div>
            <div>
                <input id="select-option-1" name="select[]" type="checkbox" value="value2">
                <label for="select-option-1">display2</label>
            </div>
        </fieldset>
        html;

        $expected = self::collapsedHtml($html);

        $result = (string) Select::create(
            label: 'Select',
            name: 'select',
            options: [
                'value'  => 'display',
                'value2' => 'display2'
            ]
        )->checkbox()->labelProps('is checkbox');

        parent::assertSame($expected, $result);

        $html = <<<html
        <fieldset>
            <legend is="radio">Select</legend>
            <div>
                <input id="select-option-0" name="select" type="radio" value="value">
                <label for="select-option-0">display</label>
            </div>
            <div>
                <input id="select-option-1" name="select" type="radio" value="value2">
                <label for="select-option-1">display2</label>
            </div>
        </fieldset>
        html;

        $expected = self::collapsedHtml($html);

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
        $html = <<<html
        <fieldset>
            <legend>Select your option</legend>
            <div>
                <input id="select-option-0" name="select[]" type="checkbox" value="value" checked>
                <label for="select-option-0">display</label>
            </div>
            <div>
                <input id="select-option-1" name="select[]" type="checkbox" value="value2" checked>
                <label for="select-option-1">display2</label>
            </div>
        </fieldset>
        html;

        $expected = self::collapsedHtml($html);

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
        $html = <<<html
        <fieldset>
            <legend>Select your option</legend>
            <div>
                <input id="select-option-0" name="select" type="radio" value="value" checked>
                <label for="select-option-0">display</label>
            </div>
        </fieldset>
        html;

        $expected = self::collapsedHtml($html);

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
        $html = <<<html
        <div>
            <label for="select">Select your option</label>
            <select id="select" name="select">
                <option value="value">display</option>
            </select>
        </div>
        html;

        $expected = self::collapsedHtml($html);

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
        $html = <<<html
        <fieldset>
            <legend>Select your option</legend>
            <div>
                <input id="select-option-0" name="select[]" type="checkbox" value="value" checked>
                <label for="select-option-0">display</label>
            </div>
        </fieldset>
        html;

        $expected = self::collapsedHtml($html);

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
        $html = <<<html
        <div id="some-id" class="some-token">
            <label for="select">Select your option</label>
            <select id="select" name="select">
                <option value="value">display</option>
            </select>
        </div>
        html;

        $expected = self::collapsedHtml($html);

        $result = (string) Select::create(
            'Select your option',
            'select',
            [
                'value' => 'display'
            ]
        )->containerProps('id some-id', 'class some-token');

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_preselect_option(): void // phpcs:ignore
    {
        $html = <<<html
        <div>
            <label for="select">Select your option</label>
            <select id="select" name="select">
                <option value="value">display</option>
                <option value="value2" selected>display2</option>
            </select>
        </div>
        html;

        $expected = self::collapsedHtml($html);

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
        $html = <<<html
        <div>
            <label for="select">Select your option</label>
            <select id="select" name="select">
                <option value="value">display</option>
            </select>
        </div>
        html;

        $expected = self::collapsedHtml($html);

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
        $html = <<<html
        <div>
            <label for="select">Select your option</label>
            <select id="select" name="select">
                <option value="0">display</option>
            </select>
        </div>
        html;

        $expected = self::collapsedHtml($html);

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
