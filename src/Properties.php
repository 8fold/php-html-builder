<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder;

final class Properties
{
    /**
     * The following attributes will be placed in this order if used in an
     * element. To change the order, extend the Element class and override
     * this constant.
     */
    public const ORDERED = [
        "is",
        "role",
        "id",
        "class",
        "style",
        "name",
        "type",
        "media",
        "tabindex",
        "accesskey",
        "width",
        "height",
        "lang",
        "srclang",
        "hreflang",
        "dir",
        "translate",
        "src",
        "rel",
        "href",
        "target",
        "itemtype",
        "itemref",
        "itemprop",
        "title",
        "http-equiv",
        "charset",
        "alt",
        "value",
        "content",
        "manifest",
        "contenteditable",
        "spellcheck",
        "start"
    ];

    /**
     * @param  list<string>  $properties
     * @param  list<string>  $ordered
     */
    public static function compile(
        array $properties,
        array $ordered = self::ORDERED
    ): string {
        if ($properties === []) {
            return '';
        }

        $o = [];
        $other   = [];
        $boolean = [];

        foreach ($properties as $property) {
            [$attr, $value] = array_pad(explode(' ', $property, 2), 2, '');

            if ($value === '') {
                continue;
            }

            if (in_array($attr, $ordered, true)) {
                $o[$attr] = $value;

            } elseif ($attr === $value) {
                $boolean[$attr] = true;

            } else {
                $other[$attr] = $value;

            }
        }

        $parts = [];

        foreach ($ordered as $attr) {
            if (isset($o[$attr])) {
                $parts[] = $attr . '="' . $o[$attr] . '"';
            }
        }

        ksort($other);
        foreach ($other as $attr => $value) {
            $parts[] = $attr . '="' . $value . '"';
        }

        ksort($boolean);
        foreach (array_keys($boolean) as $attr) {
            $parts[] = $attr;
        }

        return $parts === [] ? '' : ' ' . implode(' ', $parts);
    }

    final private function __construct()
    {
    }
}
