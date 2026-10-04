<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\PropertyInterface;

use Eightfold\HtmlBuilder\Forms\PasswordAutocomplete;

class Password implements Stringable
{
    // phpcs:disable
    private string $warningId {
        get => $this->name . '-warning';
    }
    // phpcs:enable

    /**
     * @var string[]
     */
    private array $labelProperties = [];

    /**
     * @var string[]
     */
    private array $inputProperties = [];


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

    private function warningMessage(): string|PropertyInterface
    {
        if ($this->warningMessage === '') {
            return '';
        }

        if (is_string($this->warningMessage)) {
            return Element::p($this->warningMessage)->props(
                'id ' . $this->warningId
            );
        }

        return $this->warningMessage->prop('id ' . $this->warningId);
    }

    public function __toString(): string
    {
        $elements = [];
        $elements[] = Element::label($this->label)->props(
            'for ' . $this->name,
            ...$this->labelProperties
        );

        $input = Element::input()->omitEndTag()->props(
            'type password',
            'id ' . $this->name,
            'name ' . $this->name,
            'autocomplete ' . $this->autocomplete->value,
            ...$this->inputProperties
        );

        $warningMessage = self::warningMessage();
        $hasWarningMessage = ($warningMessage !== '');
        if ($hasWarningMessage) {
            $input = $input->prop('aria-invalid true');
            $input = $input->prop('aria-describedby ' . $this->warningId);
        }
        $elements[] = $input;

        if ($hasWarningMessage) {
            $elements[] = $warningMessage;
        }

        return implode('', array_map('strval', $elements));
    }
}
