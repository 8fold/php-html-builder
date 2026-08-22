<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Forms;

use Stringable;

use Eightfold\HTMLBuilder\Element;

use Eightfold\HTMLBuilder\Forms\SelectType;

class Select implements Stringable
{
    /**
     * @var string[]
     */
    private array $wrapperProperties = [];

    private SelectType $type = SelectType::Dropdown;

    /**
     * @param array<string|int, string> $options
     * @param string|string[] $selected
     */
    public static function create(
        string|Stringable $label,
        string|Stringable $name,
        array $options,
        string|array $selected = []
    ): self {
        return new self($label, $name, $options, $selected);
    }

    /**
     * @param array<string|int, string> $options
     * @param string|string[] $selected
     */
    final private function __construct(
        private readonly string|Stringable $label,
        private readonly string|Stringable $name,
        private readonly array $options,
        private string|array $selected = []
    ) {
    }

    public function wrapperProps(string ...$properties): self
    {
        $this->wrapperProperties = $properties;
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
        $selected = $this->selected();
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
        if ($this->hasSelected() === false) {
            return false;
        }

        if (is_array($this->selected())) {
            return in_array($value, $this->selected());
        }
        return $value === $this->selected();
    }

    public function __toString(): string
    {
        if ($this->type === SelectType::Dropdown) {
            return (string) $this->selectDropdown();
        }
        return (string) $this->selectOther();
    }

    private function selectDropdown(): Element
    {
        $elements = [];
        foreach ($this->options as $value => $content) {
            $value  = (string) $value;
            $option = Element::option($content)->props('value ' . $value);
            if ($this->isSelected($value)) {
                $option = $option->prop('selected selected');
            }
            $elements[] = $option;
        }

        return Element::div(
            Element::label(
                $this->label
            )->props('for ' . $this->name),
            Element::select(
                ...$elements
            )->props('id ' . $this->name, 'name ' . $this->name)
        )->props(...$this->wrapperProperties);
    }

    private function selectOther(): Element
    {
        $elements = [];
        $type = $this->type === SelectType::Checkbox ? 'checkbox' : 'radio';
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
            if ($this->isSelected($value)) {
                $input = $input->prop('checked checked');
            }
            $elements[] = Element::div($input, $label);
        }
        return Element::fieldset(
            Element::legend($this->label),
            ...$elements
        );
    }
}
