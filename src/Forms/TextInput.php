<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\PropertyInterface;

class TextInput implements Stringable
{
    private string $id;

    /**
     * @var string[]
     */
    private array $wrapperProperties = [];

    /**
     * @var string[]
     */
    private array $labelProperties = [];

    /**
     * @var string[]
     */
    private array $inputProperties = [];

    /**
     * @var array<string|Stringable>
     */
    private array $content = [];

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
        $this->wrapperProperties = $properties;
        return $this;
    }

    public function labelProps(string ...$properties): self
    {
        $this->labelProperties = $properties;
        return $this;
    }

    public function inputProps(string ...$properties): self
    {
        $this->inputProperties = $properties;
        return $this;
    }

    public function content(string|Stringable ...$content): self
    {
        $this->content = $content;
        return $this;
    }

    private function warningMessage(string $warningId): string|PropertyInterface
    {
        if ($this->warningMessage === '') {
            return '';
        }

        if (is_string($this->warningMessage)) {
            return Element::p($this->warningMessage)->props(
                'id ' . $warningId
            );
        }

        $warning = clone $this->warningMessage;

        return $warning->prop('id ' . $warningId);
    }

    public function __toString(): string
    {
        $inputProperties = $this->warningMessage === ''
            ? $this->inputProperties
            : ControlProperties::withWarning(
                array_values($this->inputProperties),
                $this->id . '-warning'
            );

        $input = Element::input()->omitEndTag()->props(...[
            ...$inputProperties,
            'type text',
            'id ' . $this->id,
            'name ' . $this->name,
            'value ' . $this->value
        ]);

        $warning = '';
        if ($this->warningMessage !== '') {
            $warningId = $this->id . '-warning';

            $warning = (string) self::warningMessage($warningId);
        }

        return (string) Element::div(
            Element::label($this->label)->props(...[
                ...$this->labelProperties,
                'for ' . $this->id
            ]),
            $input,
            ...[...$this->content, $warning]
        )->props(...$this->wrapperProperties);
    }
}
