<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\PropertyInterface;

use Eightfold\HtmlBuilder\Forms\PasswordAutocomplete;

class Password implements Stringable
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

    private PasswordAutocomplete $autocomplete = PasswordAutocomplete::CurrentPassword;

    public static function create(
        string|Stringable $label,
        string|Stringable $name,
        string|PropertyInterface $warningMessage = ''
    ): self {
        return new self($label, $name, $warningMessage);
    }

    final private function __construct(
        private readonly string|Stringable $label,
        private readonly string|Stringable $name,
        private readonly string|PropertyInterface $warningMessage = ''
    ) {
        $this->id = $name;
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

    public function forNewPassword(): self
    {
        $this->autocomplete = PasswordAutocomplete::NewPassword;
        return $this;
    }

    public function autocompleteOff(): self
    {
        $this->autocomplete = PasswordAutocomplete::Off;
        return $this;
    }

    public function autocompleteOn(): self
    {
        $this->autocomplete = PasswordAutocomplete::On;
        return $this;
    }

    public function __toString(): string
    {
        $warning         = self::warningMessage();
        $inputProperties = self::inputProperties($warning === '');

        $input = Element::input()->omitEndTag()->props(...[
            ...$inputProperties,
            'type password',
            'id ' . $this->id,
            'name ' . $this->name,
            'autocomplete ' . $this->autocomplete->value,
        ]);

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
