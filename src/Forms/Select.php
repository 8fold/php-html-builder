<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\PropertyInterface;

use Eightfold\HtmlBuilder\Forms\SelectType;

class Select implements Stringable
{
    // phpcs:disable
    private string $warningId {
        get => $this->name . '-warning';
    }
    // phpcs:enable

    /**
     * @var string[]
     */
    private array $wrapperProperties = [];

    /**
     * @var string[]
     */
    private array $labelProperties = [];

    private SelectType $type = SelectType::Dropdown;

    /**
     * @param array<string|int, string> $options
     * @param string|string[] $selected
     */
    public static function create(
        string|Stringable $label,
        string|Stringable $name,
        array $options,
        string|array $selected = [],
        string|PropertyInterface $warningMessage = ''
    ): self {
        return new self($label, $name, $options, $selected, $warningMessage);
    }

    /**
     * @param array<string|int, string> $options
     * @param string|string[] $selected
     */
    final private function __construct(
        private readonly string|Stringable $label,
        private readonly string|Stringable $name,
        private readonly array $options,
        private readonly string|array $selected = [],
        private readonly string|PropertyInterface $warningMessage = ''
    ) {
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

    public function dropdown(): self
    {
        $this->type = SelectType::Dropdown;
        return $this;
    }

    public function radio(): self
    {
        $this->type = SelectType::Radio;
        return $this;
    }

    public function checkbox(): self
    {
        $this->type = SelectType::Checkbox;
        return $this;
    }

    private function hasSelected(): bool
    {
        $selected = self::selected();
        if (is_string($selected) and strlen($selected) > 0) {
            return true;

        } elseif (is_array($selected) and count($selected) > 0) {
            return true;

        }
        return false;
    }

    /**
     * @return string|string[]
     */
    private function selected(): string|array
    {
        if ($this->type === SelectType::Checkbox) {
            if (is_array($this->selected)) {
                return $this->selected;
            }
            return [$this->selected];
        }

        if (is_array($this->selected) and count($this->selected) > 0) {
            return $this->selected[0];
        }
        return $this->selected;
    }

    private function isSelected(string $value): bool
    {
        if (self::hasSelected() === false) {
            return false;
        }

        if (is_array(self::selected())) {
            return in_array($value, self::selected());
        }
        return $value === self::selected();
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
        if ($this->type === SelectType::Dropdown) {
            return (string) self::selectDropdown();
        }
        return (string) self::selectOther();
    }

    private function selectDropdown(): Element
    {
        $elements = [];
        foreach ($this->options as $value => $content) {
            $value  = (string) $value;
            $option = Element::option($content)->props('value ' . $value);
            if (self::isSelected($value)) {
                $option = $option->prop('selected selected');
            }
            $elements[] = $option;
        }

        $input = Element::select(
            ...$elements
        )->props('id ' . $this->name, 'name ' . $this->name);
        if (self::warningMessage() !== '') {
            $input = $input->prop('aria-invalid true');
            $input = $input->prop('aria-describedby ' . $this->warningId);
        }

        return Element::div(
            Element::label(
                $this->label
            )->props('for ' . $this->name, ...$this->labelProperties),
            $input,
            self::warningMessage()
        )->props(...$this->wrapperProperties);
    }

    private function selectOther(): Element
    {
        $elements = [];
        $type = $this->type === SelectType::Checkbox ? 'checkbox' : 'radio';

        $hasWarningMessage = (self::warningMessage() !== '');
        foreach ($this->options as $value => $content) {
            $value = (string) $value;
            $id    = $this->name . '-' . $value;
            $label = Element::label($content)->props('for ' . $id);
            $input = Element::input()->omitEndTag()->props(
                'id ' . $id,
                ($this->type === SelectType::Checkbox)
                    ? 'name ' . $this->name . '[]'
                    : 'name ' . $this->name,
                'type ' . $type,
                'value ' . $value
            );

            if (self::isSelected($value)) {
                $input = $input->prop('checked checked');
            }

            if ($hasWarningMessage) {
                $input = $input->prop('aria-invalid true');
                $input = $input->prop('aria-describedby ' . $this->warningId);
            }
            $elements[] = Element::div($input, $label);
        }

        if ($hasWarningMessage) {
            $elements[] = self::warningMessage();
        }

        return Element::fieldset(
            Element::legend($this->label)->props(...$this->labelProperties),
            ...$elements
        );
    }
}
