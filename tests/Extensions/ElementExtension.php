<?php

declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Tests\Extensions;

use Eightfold\HTMLBuilder\Element as HTMLElement;

class ElementExtension extends HTMLElement
{
    /**
     * The following attributes will be placed in this order if used in an
     * element. To change the order, extend the Element class and override
     * this constant.
     */
    const ORDERED = [
        "is",
        "role",
        "class", // modified from default
        "href", // modified from default
        "id", // modified from default
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
}
