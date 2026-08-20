<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder;

use Stringable;

use Eightfold\XMLBuilder\Element as XMLElement;

class Element extends XMLElement implements Stringable
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
        "name",
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

    protected function omitEndTagClosingString(): string
    {
        return '>';
    }

    protected function propertiesString(): string
    {
        if ($this->properties() === []) {
            return '';
        }

        $ordered = [];
        $other = [];
        $boolean = [];

        foreach ($this->properties() as $property) {
            [$attr, $value] = array_pad(explode(' ', $property, 2), 2, '');

            if ($value === '') {
                continue;
            }

            if (in_array($attr, self::ORDERED, true)) {
                $ordered[$attr] = $value;
            } elseif ($attr === $value) {
                $boolean[$attr] = true;
            } else {
                $other[$attr] = $value;
            }
        }

        $parts = [];

        foreach (self::ORDERED as $attr) {
            if (isset($ordered[$attr])) {
                $parts[] = $attr . '="' . $ordered[$attr] . '"';
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
}
