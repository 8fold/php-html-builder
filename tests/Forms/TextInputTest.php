<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Forms\TextInput;

class TextInputTest extends TestCase
{
    #[Test]
    public function can_set_value(): void // phpcs:ignore
    {
        $expected = <<<html
        <label for="text">Text</label><input id="text" name="text" type="text" value="Posted value">
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
        <label for="text">Text</label><input id="text" name="text" type="text" aria-describedby="text-warning" aria-invalid="true"><p id="text-warning">Invalid value</p>
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
        <label for="text">Text</label><input id="text" name="text" type="text">
        html;

        $result = (string) TextInput::create(
            'Text',
            'text'
        );

        parent::assertSame($expected, $result);
    }
}
