<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Tests\Components;

use PHPUnit\Framework\TestCase;

use Eightfold\HTMLBuilder\Components\Favicons;

use Eightfold\HTMLBuilder\Components\FaviconMetroColors;

class FaviconsTest extends TestCase
{
    /**
     * @test
     */
    public function can_use_default(): void
    {
        $expected = file_get_contents(__DIR__ . '/favicons-default.xml');

        $result = (string) Favicons::create();

        $this->assertSame(
            $expected,
            $result . "\n"
        );
    }

    /**
     * @test
     */
    public function can_set_theme_color(): void
    {
        $expected = file_get_contents(__DIR__ . '/favicons-theme-color.xml');

        $result = (string) Favicons::create(
            themeColor: '#000000'
        );

        $this->assertSame(
            $expected,
            $result . "\n"
        );
    }

    /**
     * @test
     */
    public function can_use_subfolder(): void
    {
        $expected = file_get_contents(__DIR__ . '/favicons-subfolder.xml');

        $result = (string) Favicons::create('/subfolder');

        $this->assertSame(
            $expected,
            $result . "\n"
        );
    }

    /**
     * @test
     */
    public function can_use_custom_app_name(): void
    {
        $expected = file_get_contents(__DIR__ . '/favicons-app-name.xml');

        $result = (string) Favicons::create()->withAppName('Override page title');

        $this->assertSame(
            $expected,
            $result . "\n"
        );
    }

    /**
     * @test
     */
    public function can_use_windows_metro_settings(): void
    {
        $expected = file_get_contents(__DIR__ . '/favicons-windows-metro.xml');

        $result = (string) Favicons::create()->withMetro(useWhite: true);

        $this->assertSame(
            $expected,
            $result . "\n"
        );

        $expected = file_get_contents(
            __DIR__ . '/favicons-windows-metro-tile-color.xml'
        );

        $result = (string) Favicons::create()->withMetro(
            FaviconMetroColors::Teal
        );

        $this->assertSame(
            $expected,
            $result . "\n"
        );
    }
}
