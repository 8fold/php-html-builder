<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Forms\EmailInput;

class EmailInputTest extends TestCase
{
    #[Test]
    public function can_set_value(): void // phpcs:ignore
    {
        $expected = <<<html
        <label for="email">Email</label><input id="email" name="email" type="email" value="support@8fold.pro">
        html;

        $result = (string) EmailInput::create(
            'Email',
            'email',
            'support@8fold.pro'
        );

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_set_warning_message(): void // phpcs:ignore
    {
        $expected = <<<html
        <label for="email">Email</label><input id="email" name="email" type="email" aria-describedby="email-warning" aria-invalid="true"><p id="email-warning">Invalid value</p>
        html;

        $result = (string) EmailInput::create(
            label: 'Email',
            name: 'email',
            warningMessage: 'Invalid value'
        );

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function is_expected_base(): void // phpcs:ignore
    {
        $expected = <<<html
        <label for="email">Email</label><input id="email" name="email" type="email">
        html;

        $result = (string) EmailInput::create(
            'Email',
            'email'
        );

        parent::assertSame($expected, $result);
    }
}
