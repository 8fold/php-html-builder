<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Tests\Components;

use PHPUnit\Framework\TestCase;

use Eightfold\HTMLBuilder\Components\Copyright;

class CopyrightTest extends TestCase
{
    /**
     * @test
     */
    public function can_use_default(): void
    {
        $expected = '<p>© ' . date('Y') . ' Dr. Copyright</p>';

        $result = (string) Copyright::create('Dr. Copyright');

        $this->assertSame(
            $expected,
            $result
        );
    }

    /**
     * @test
     */
    public function can_use_bare_string(): void
    {
        $expected = '© ' . date('Y') . ' Dr. Copyright';

        $result = (string) Copyright::create('Dr. Copyright')->stringOnly();

        $this->assertSame(
            $expected,
            $result
        );

        $expected = '© ' . date('Y') . ' Dr. Copyright';

        $result = (string) Copyright::create(
            'Dr. Copyright',
            wrapInParagraph: false
        );

        $this->assertSame(
            $expected,
            $result
        );
    }

    /**
     * @test
     */
    public function can_have_year_range(): void
    {
        $expected = '<p>© 2020–' . date('Y') . ' Dr. Copyright</p>';

        $result = (string) Copyright::create('Dr. Copyright', '2020');

        $this->assertSame(
            $expected,
            $result
        );
    }

    /**
     * @test
     *
     * @group focus
     */
    public function can_include_copyright(): void
    {
        $expected = '<p>Copyright ' . date('Y') . ' Dr. Copyright</p>';

        $result = (string) Copyright::create('Dr. Copyright')
            ->spellOutCopyright();

        $this->assertSame(
            $expected,
            $result
        );

        $expected = '<p>Copyright © ' . date('Y') . ' Dr. Copyright</p>';

        $result = (string) Copyright::create('Dr. Copyright')
            ->expandedCopyright();

        $this->assertSame(
            $expected,
            $result
        );
    }

    /**
     * @test
     */
    public function can_modify_reservations(): void
    {
        $expected = '<p>© ' . date('Y') . ' Dr. Copyright. All rights reserved.</p>';

        $result = (string) Copyright::create('Dr. Copyright')
            ->allRightsReserved();

        $this->assertSame(
            $expected,
            $result
        );

        $expected = '<p>© ' . date('Y') . ' Dr. Copyright. Some rights reserved.</p>';

        $result = (string) Copyright::create('Dr. Copyright')
            ->someRightsReserved();

        $this->assertSame(
            $expected,
            $result
        );

        $expected = '<p>© ' . date('Y') . ' Dr. Copyright. No rights reserved.</p>';

        $result = (string) Copyright::create('Dr. Copyright')
            ->noRightsReserved();

        $this->assertSame(
            $expected,
            $result
        );
    }
}
