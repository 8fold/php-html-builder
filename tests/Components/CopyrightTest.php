<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests\Components;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Components\Copyright;

class CopyrightTest extends TestCase
{
    #[Test]
    public function can_use_default(): void
    {
        $expected = '<p>© ' . date('Y') . ' Dr. Copyright</p>';

        $result = (string) Copyright::create('Dr. Copyright');

        parent::assertSame(
            $expected,
            $result
        );
    }

    #[Test]
    public function can_use_bare_string(): void
    {
        $expected = '© ' . date('Y') . ' Dr. Copyright';

        $result = (string) Copyright::create('Dr. Copyright')->stringOnly();

        parent::assertSame(
            $expected,
            $result
        );

        $expected = '© ' . date('Y') . ' Dr. Copyright';

        $result = (string) Copyright::create(
            'Dr. Copyright',
            wrapInParagraph: false
        );

        parent::assertSame(
            $expected,
            $result
        );
    }

    #[Test]
    public function can_have_year_range(): void
    {
        $expected = '<p>© 2020–' . date('Y') . ' Dr. Copyright</p>';

        $result = (string) Copyright::create('Dr. Copyright', '2020');

        parent::assertSame(
            $expected,
            $result
        );
    }

    #[Test]
    public function can_include_copyright(): void
    {
        $expected = '<p>Copyright ' . date('Y') . ' Dr. Copyright</p>';

        $result = (string) Copyright::create('Dr. Copyright')
            ->spellOutCopyright();

        parent::assertSame(
            $expected,
            $result
        );

        $expected = '<p>Copyright © ' . date('Y') . ' Dr. Copyright</p>';

        $result = (string) Copyright::create('Dr. Copyright')
            ->expandedCopyright();

        parent::assertSame(
            $expected,
            $result
        );
    }

    #[Test]
    public function can_modify_reservations(): void
    {
        $expected = '<p>© ' . date('Y') . ' Dr. Copyright. All rights reserved.</p>';

        $result = (string) Copyright::create('Dr. Copyright')
            ->allRightsReserved();

        parent::assertSame(
            $expected,
            $result
        );

        $expected = '<p>© ' . date('Y') . ' Dr. Copyright. Some rights reserved.</p>';

        $result = (string) Copyright::create('Dr. Copyright')
            ->someRightsReserved();

        parent::assertSame(
            $expected,
            $result
        );

        $expected = '<p>© ' . date('Y') . ' Dr. Copyright. No rights reserved.</p>';

        $result = (string) Copyright::create('Dr. Copyright')
            ->noRightsReserved();

        parent::assertSame(
            $expected,
            $result
        );
    }
}
