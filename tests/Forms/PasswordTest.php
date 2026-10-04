<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Forms\Password;

class PasswordTest extends TestCase
{
    #[Test]
    public function can_set_warning_message(): void // phpcs:ignore
    {
        $expected = <<<html
        <label for="password">Password</label><input id="password" name="password" type="password" aria-describedby="password-warning" aria-invalid="true" autocomplete="new-password"><p id="password-warning">Invalid value</p>
        html;

        $result = (string) Password::create(
            'Password',
            'password',
            'Invalid value'
        )->forNewPassword();

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function can_change_autocomplete(): void // phpcs:ignore
    {
        $expected = <<<html
        <label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password">
        html;

        $result = (string) Password::create(
            'Password',
            'password'
        )->forNewPassword();

        parent::assertSame($expected, $result);

        $expected = <<<html
        <label for="password">Password</label><input id="password" name="password" type="password" autocomplete="on">
        html;

        $result = (string) Password::create(
            'Password',
            'password'
        )->autocompleteOn();

        parent::assertSame($expected, $result);

        $expected = <<<html
        <label for="password">Password</label><input id="password" name="password" type="password" autocomplete="off">
        html;

        $result = (string) Password::create(
            'Password',
            'password'
        )->autocompleteOff();

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function is_expected_base(): void // phpcs:ignore
    {
        $expected = <<<html
        <label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password">
        html;

        $result = (string) Password::create(
            'Password',
            'password'
        );

        parent::assertSame($expected, $result);
    }
}
