<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Forms\EmailInput;

use Eightfold\HtmlBuilder\Element;

class EmailInputTest extends TestCase
{
    #[Test]
    public function can_set_custom_id(): void // phpcs:ignore
    {
        $expected = <<<html
        <div is="form-control"><label for="email-input-id">Email</label><input id="email-input-id" name="email" type="email" aria-describedby="email-input-id-warning" aria-invalid="true" autocapitalize="off" autocorrect="off"><p id="email-input-id-help">Helpful text</p><p id="email-input-id-warning">Invalid value</p></div>
        html;

        $result = (string) EmailInput::create(
            label: 'Email',
            name: 'email',
            warningMessage: 'Invalid value'
        )->id('email-input-id')->content(
            Element::p('Helpful text')->props('id email-input-id-help')
        )->containerProps('is form-control');

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_set_value(): void // phpcs:ignore
    {
        $expected = <<<html
        <div><label for="email">Email</label><input id="email" name="email" type="email" value="support@8fold.pro" autocapitalize="off" autocorrect="off"></div>
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
        <div><label for="email">Email</label><input id="email" name="email" type="email" aria-describedby="email-warning" aria-invalid="true" autocapitalize="off" autocorrect="off"><p id="email-warning">Invalid value</p></div>
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
        <div><label for="email">Email</label><input id="email" name="email" type="email" autocapitalize="off" autocorrect="off"></div>
        html;

        $result = (string) EmailInput::create(
            'Email',
            'email'
        );

        parent::assertSame($expected, $result);
    }
}
