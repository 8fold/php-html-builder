<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Tests;

use PHPUnit\Framework\TestCase;

// use Eightfold\HTMLBuilder\Tests\Extensions\ElementExtension;

// use Eightfold\XMLBuilder\Comment;

// use Eightfold\HTMLBuilder\Document;
use Eightfold\HTMLBuilder\Forms\Select;

class SelectTest extends TestCase
{
    /**
     * @test
     */
    public function can_be_checkboxes(): void // phpcs: ignore
    {
        $expected = <<<html
        <fieldset><legend>Select your option</legend><div><input id="select-value" type="checkbox" value="value" checked><label for="select-value">display</label></div><div><input id="select-value2" type="checkbox" value="value2" checked><label for="select-value2">display2</label></div></fieldset>
        html;

        $result = (string) Select::create(
            'Select your option',
            'select',
            [
                'value'  => 'display',
                'value2' => 'display2'
            ],
            ['value', 'value2']
        )->checkbox();

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function can_be_radio_buttons(): void // phpcs: ignore
    {
        $expected = <<<html
        <fieldset><legend>Select your option</legend><div><input id="select-value" type="radio" value="value" checked><label for="select-value">display</label></div></fieldset>
        html;

        $result = (string) Select::create(
            'Select your option',
            'select',
            [
                'value' => 'display'
            ],
            'value'
        )->radio();

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
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

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
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

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
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

        $this->assertSame($expected, $result);
    }
}
