<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Forms;

use Stringable;

use Eightfold\HTMLBuilder\Element;

class Select implements Stringable
{
    private array $wrapperProperties = [];

    private bool $dropdown = true;

    private bool $radio = false;

    private bool $checkbox = false;

    public static function create(
        string|Stringable $label,
        string|Stringable $name,
        array $options,
        string|array $selected = []
    ): self {
        return new self($label, $name, $options, $selected);
    }

    final private function __construct(
        private readonly string|Stringable $label,
        private readonly string|Stringable $name,
        private readonly array $options,
        private string|array $selected = []
    ) {
    }

    public function wrapperProps(string ...$propperties): self
    {
        $this->wrapperProperties = $propperties;
        return $this;
    }

    public function dropdown(): self
    {
        $this->dropdown = true;

        $this->radio    = false;
        $this->checkbox = false;
        return $this;
    }

    public function radio(): self
    {
        $this->radio = true;

        $this->dropdown = false;
        $this->checkbox = false;
        return $this;
    }

    public function checkbox(): self
    {
        $this->checkbox = true;

        $this->radio    = false;
        $this->dropdown = false;
        return $this;
    }

    private function hasSelected(): bool
    {
        if (is_string($this->selected) and strlen($this->selected) > 0) {
            return true;

        } elseif (count($this->selected) > 0) {
            return true;
        }
        return false;
    }

    private function selected(): string|array
    {
        if ($this->checkbox) {
            if (is_array($this->selected)) {
                return $this->selected;
            }
            return [$this->selected];
        }

        if (is_array($this->selected)) {
            return $this->selected[0];
        }
        return $this->selected;
    }

    private function isSelected(string $value): bool
    {
        if ($this->hasSelected() === false) {
            return false;
        }

        if ($this->checkbox) {
            return in_array($value, $this->selected());
        }
        return $value === $this->selected();
    }

    public function __toString(): string
    {
        if ($this->dropdown) {
            return (string) $this->selectDropdown();
        }
        return (string) $this->selectOther();
    }

    private function selectDropdown(): Element
    {
        $elements = [];
        foreach ($this->options as $value => $content) {
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
        $type = 'radio';
        if ($this->checkbox) {
            $type = 'checkbox';
        }
        foreach ($this->options as $value => $content) {
            $id    = $this->name . '-' . $value;
            $label = Element::label($content)->props('for ' . $id);
            $input = Element::input()->omitEndTag()->props(
                'id ' . $id,
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
