<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Components;

use Stringable;

use Eightfold\XMLBuilder\Concatenate;

use Eightfold\HTMLBuilder\Element;

use Eightfold\HTMLBuilder\Components\FaviconMetroColors;

/**
 * We use https://realfavicongenerator.net to generate favicon-related assets.
 * We presume the names of these assets will not be changed.
 */
class Favicons implements Stringable
{
    private string $appName = '';

    private bool $metroUsesWhite;

    private FaviconMetroColors|string $metroTileColor = FaviconMetroColors::DarkOrange;

    private string $safariThemeColor = '#5bbad5';

    public static function create(
        string $path = '',
        string $themeColor = '#ffffff'
    ): self {
        return new self($path, $themeColor);
    }

    final private function __construct(
        private string $path,
        private string $themeColor
    ) {
    }

    private function path(): string
    {
        return $this->path;
    }

    private function themeColor(): string
    {
        return $this->themeColor;
    }

    private function hasPath(): bool
    {
        return strlen($this->path()) > 0;
    }

    private function appName(): string
    {
        return $this->appName;
    }

    public function withAppName(string $name): self
    {
        $this->appName = $name;
        return $this;
    }

    private function hasAppName(): bool
    {
        return strlen($this->appName()) > 0;
    }

    public function withMetro(
        FaviconMetroColors|string $tileColor = FaviconMetroColors::DarkOrange,
        bool $useWhite = false
    ): self {
        $this->metroTileColor = $tileColor;

        if ($useWhite !== false) {
            $this->metroUsesWhite = $useWhite;
        }

        return $this;
    }

    private function hasMetro(): bool
    {
        if ($this->metroUsesWhite()) {
            return true;
        }
        return false;
    }

    private function metroUsesWhite(): bool
    {
        if (isset($this->metroUsesWhite) === false) {
            return false;
        }
        return $this->metroUsesWhite;
    }

    private function metroTileColor(): string
    {
        if (is_string($this->metroTileColor)) {
            return $this->metroTileColor;
        }
        return $this->metroTileColor->value;
    }

    public function withSafariThemeColor(string $color): self
    {
        $this->safariThemeColor = $color;
        return $this;
    }

    private function safariThemeColor(): string
    {
        return $this->safariThemeColor;
    }

    public function __toString(): string
    {
        $elements = [
            Element::link()->omitEndTag()->props(
                'rel apple-touch-icon',
                'sizes 180x180',
                'href ' . $this->path() . '/apple-touch-icon.png'
            ),
            Element::link()->omitEndTag()->props(
                'rel icon',
                'type image/png',
                'sizes 32x32',
                'href ' . $this->path() . '/favicon-32x32.png'
            ),
            Element::link()->omitEndTag()->props(
                'rel icon',
                'type image/png',
                'sizes 16x16',
                'href ' . $this->path() . '/favicon-16x16.png'
            ),
            Element::link()->omitEndTag()->props(
                'rel manifest',
                'href ' . $this->path() . '/site.webmanifest'
            ),
            Element::meta()->omitEndTag()->props(
                'name msapplication-TileColor',
                'content ' . $this->metroTileColor()
            ),
            Element::meta()->omitEndTag()->props(
                'name theme-color',
                'content ' . $this->themeColor()
            ),
            Element::link()->omitEndTag()->props(
                'rel mask-icon',
                'href ' . $this->path() . '/safari-pinned-tab.svg',
                'color ' . $this->safariThemeColor()
            )
        ];

        if ($this->hasPath()) {
            $elements[] = Element::link()->omitendTag()->props(
                'rel shortcut icon',
                'href ' . $this->path() . '/favicon.ico'
            );

            $elements[] = Element::meta()->omitEndTag()->props(
                'name msapplication-config',
                'content ' . $this->path() . '/browserconfig.xml'
            );
        }

        if ($this->hasMetro() and $this->metroUsesWhite()) {
            $elements[] = Element::meta()->omitEndTag()->props(
                'name msapplication-TileImage',
                'content ' . $this->path() . '/mstile-144x144.png'
            );
        }

        if ($this->hasAppName()) {
            $elements[] = Element::meta()->omitEndTag()->props(
                'name application-name',
                'content ' . $this->appName()
            );

            $elements[] = Element::meta()->omitEndTag()->props(
                'name apple-mobile-web-app-title',
                'content ' . $this->appName()
            );
        }
        return (string) Concatenate::create(...$elements);
    }
}
