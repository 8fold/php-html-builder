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
        if (count($this->properties()) === 0) {
            return '';
        }

        $orderedAttributes = array_fill_keys(static::ORDERED, "");
        $otherAttributes   = [];
        $booleanAttributes = [];

        $build = [];
        foreach ($this->properties() as $property) {
            if (strlen($property) === 0) {
                continue;
            }

            list($attr, $content) = explode(' ', $property, 2);

            if (strlen($content) > 0) {
                if (array_key_exists($attr, $orderedAttributes)) {
                    $orderedAttributes[$attr] = $content;

                } elseif ($attr === $content) {
                    $booleanAttributes[$attr] = $content;

                } else {
                    $otherAttributes[$attr] = $content;

                }
            }

            // Logic from previous implementation using php-html-spec to
            // query and validate.
            //
            // if (array_key_exists($attr, $orderedAttributes)) {
            //     $orderedAttributes[$attr] = $content;

            // } elseif ($index->hasComponentNamed($attr) and
            //     $a = $index->componentNamed($attr)
            // ) {
            //     if ($a->isEvent()) {
            //         $eventAttributes[$attr] = $content;

            //     } elseif ($a->isData()) {
            //         $dataAttributes[$attr] = $content;

            //     } elseif ($a->isGlobal()) {
            //         $globalAttributes[$attr] = $content;

            //     } elseif ($a->isOther()) {
            //         $otherAttributes[$attr] = $content;

            //     } elseif ($a->isBoolean()) {
            //         $booleanAttributes[$attr] = $content;

            //     }

            // } else {
            //     $otherAttributes[$attr] = $content;

            // }
        }

        $orderedAttributes = array_filter($orderedAttributes, fn($c) => strlen($c) > 0);

        // $eventAttributes = array_filter($eventAttributes);
        // ksort($eventAttributes);

        // $dataAttributes = array_filter($dataAttributes);
        // ksort($eventAttributes);

        // $globalAttributes = array_filter($globalAttributes);
        // ksort($globalAttributes);

        // $otherAttributes = array_filter($otherAttributes, fn($c) => strlen($c) > 0);
        ksort($otherAttributes);

        // $booleanAttributes = array_filter($booleanAttributes);
        ksort($booleanAttributes);

        // $merged = array_merge(
        //     $orderedAttributes,
        //     // $eventAttributes,
        //     // $dataAttributes,
        //     // $globalAttributes,
        //     $otherAttributes,
        //     $booleanAttributes
        // );

        $b = [];
        foreach ($orderedAttributes as $prop => $content) {
            $b[] = $prop . '="' . $content . '"';
        }

        foreach ($otherAttributes as $prop => $content) {
            $b[] = $prop . '="' . $content . '"';
        }

        foreach ($booleanAttributes as $prop => $content) {
            $b[] = $prop;
        }

        if (count($b) === 0) {
            return '';
        }
        return ' ' . implode(' ', $b);
    }
}
