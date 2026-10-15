<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Forms\TextInput;

use Eightfold\HtmlBuilder\Element;

class TextInputTest extends TestCase
{
    #[Test]
    public function can_set_custom_id(): void // phpcs:ignore
    {
        $expected = <<<html
        <div is="form-control"><label for="text-input-id">Text</label><input id="text-input-id" name="text" type="text" aria-describedby="text-input-id-warning" aria-invalid="true"><p id="text-input-id-help">Helpful text</p><p id="text-input-id-warning">Invalid value</p></div>
        html;

        $result = (string) TextInput::create(
            label: 'Text',
            name: 'text',
            warningMessage: 'Invalid value'
        )->id('text-input-id')->content(
            Element::p('Helpful text')->props('id text-input-id-help')
        )->wrapperProps('is form-control');

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_set_value(): void // phpcs:ignore
    {
        $expected = <<<html
        <div><label for="text">Text</label><input id="text" name="text" type="text" value="Posted value"></div>
        html;

        $result = (string) TextInput::create(
            'Text',
            'text',
            'Posted value'
        );

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_set_warning_message(): void // phpcs:ignore
    {
        $expected = <<<html
        <div><label for="text">Text</label><input id="text" name="text" type="text" aria-describedby="text-warning" aria-invalid="true"><p id="text-warning">Invalid value</p></div>
        html;

        $result = (string) TextInput::create(
            label: 'Text',
            name: 'text',
            warningMessage: 'Invalid value'
        );

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function is_expected_base(): void // phpcs:ignore
    {
        $expected = <<<html
        <div><label for="text">Text</label><input id="text" name="text" type="text"></div>
        html;

        $result = (string) TextInput::create(
            'Text',
            'text'
        );

        parent::assertSame($expected, $result);
    }
}
