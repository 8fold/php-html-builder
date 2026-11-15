<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\EmptyElement;
use Eightfold\HtmlBuilder\PropertyInterface;

class Hidden implements PropertyInterface
{
    /**
     * @var list<string>
     */
    private array $inputProperties = [];

    /**
     * @var list<string|Stringable>
     */
    private array $content = [];

    public static function create(
        string|Stringable $name,
        string $value = '',
    ): self {
        return new self($name, $value);
    }

    final private function __construct(
        private readonly string|Stringable $name,
        private readonly string $value,
    ) {
    }

    public function props(string ...$properties): static
    {
        $this->inputProperties = array_values($properties);
        return $this;
    }

    public function prop(string $prop): static
    {
        $this->inputProperties[] = $prop;
        return $this;
    }

    public function content(string|Stringable ...$content): self
    {
        $this->content = array_values($content);
        return $this;
    }

    public function __toString(): string
    {
        return (string) Element::input()->omitEndTag()->props(
            'type hidden',
            ...[
                'name ' . $this->name,
                'value ' . $this->value,
                ...$this->inputProperties
            ]
        );
    }
}
