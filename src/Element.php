<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder;

use Stringable;

// use Eightfold\XMLBuilder\Element as XMLElement;

class Element implements Stringable
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

    public static function create(string $name, string|Stringable ...$content): static
    {
        return new static($name, $content);
    }

    public static function __callStatic(string $name, array $content = []): static
    {
        return new static($name, $content);
    }

    private function __construct(
        private string $name,
        private array $content = [],
        private array $properties = [],
        private bool $omitEndTag = false,
    ) {
    }

    public function props(string ...$properties): static
    {
        $this->properties = $properties;
        return $this;
    }

    public function prop(string $prop): static
    {
        $this->properties[] = $prop;
        return $this;
    }

    public function omitEndTag(): self
    {
        $this->omitEndTag = true;
        return $this;
    }

    public function toString(): string
    {
        return $this->opening()
            . implode('', array_map('strval', $this->content))
            . $this->closing();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function opening(): string
    {
        if ($this->omitEndTag) {
            return '<' . $this->name . $this->propertiesString() . '>';
        }

        return '<' . $this->name . $this->propertiesString() . '>';
    }

    private function closing(): string
    {
        return $this->omitEndTag ? '' : '</' . $this->name . '>';
    }

    protected function propertiesString(): string
    {
        if ($this->properties === []) {
            return '';
        }

        $ordered = [];
        $other   = [];
        $boolean = [];

        foreach ($this->properties as $property) {
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
