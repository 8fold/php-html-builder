<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\PropertyInterface;

class TextArea implements Stringable
{
    private string $id;

    /**
     * @var list<string>
     */
    private array $wrapperProperties = [];

    /**
     * @var list<string>
     */
    private array $labelProperties = [];

    /**
     * @var list<string>
     */
    private array $inputProperties = [];

    /**
     * @var list<string|Stringable>
     */
    private array $content = [];

    /**
     * @param string $value Caller-sanitized content, rendered without escaping.
     */
    public static function create(
        string|Stringable $label,
        string|Stringable $name,
        string $value = '',
        string|PropertyInterface $warningMessage = ''
    ): self {
        return new self($label, $name, $value, $warningMessage);
    }

    final private function __construct(
        private readonly string|Stringable $label,
        private readonly string|Stringable $name,
        private readonly string $value,
        private readonly string|PropertyInterface $warningMessage = ''
    ) {
        $this->id = (string) $name;
    }

    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function wrapperProps(string ...$properties): self
    {
        $this->wrapperProperties = array_values($properties);
        return $this;
    }

    public function labelProps(string ...$properties): self
    {
        $this->labelProperties = array_values($properties);
        return $this;
    }

    public function inputProps(string ...$properties): self
    {
        $this->inputProperties = array_values($properties);
        return $this;
    }

    public function content(string|Stringable ...$content): self
    {
        $this->content = array_values($content);
        return $this;
    }

    private function warningMessage(): string
    {
        if ($this->warningMessage === '') {
            return '';
        }

        if (is_string($this->warningMessage)) {
            return (string) Element::p($this->warningMessage)->props(
                'id ' . $this->id . '-warning'
            );
        }

        $warning = clone $this->warningMessage;

        return (string) $warning->prop('id ' . $this->id . '-warning');
    }

    /**
     * @return list<string>
     */
    private function inputProperties(bool $noWarning): array
    {
        return ($noWarning)
            ? $this->inputProperties
            : ControlProperties::withWarning(
                $this->inputProperties,
                $this->id . '-warning'
            );
    }

    public function __toString(): string
    {
        $warning         = self::warningMessage();
        $inputProperties = self::inputProperties($warning === '');

        $textarea = Element::textarea(
            "\n" . $this->value
        )->props(...[
            ...$inputProperties,
            'id ' . $this->id,
            'name ' . $this->name
        ]);

        return (string) Element::div(
            Element::label($this->label)->props(...[
                ...$this->labelProperties,
                'for ' . $this->id
            ]),
            $textarea,
            ...[...$this->content, $warning]
        )->props(...$this->wrapperProperties);
    }
}
