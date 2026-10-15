<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\PropertyInterface;

use Eightfold\HtmlBuilder\Forms\SelectType;
use Eightfold\HtmlBuilder\Forms\Fieldset;

class Select implements Stringable
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

    public function dropdown(): self
    {
        $this->type = SelectType::Dropdown;
        return $this;
    }

    public function multiple(): self
    {
        $this->type = SelectType::Multiple;
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
        if (is_string($selected) and $selected !== '') {
            return true;

        } elseif (is_array($selected) and $selected !== 0) {
            return true;

        }
        return false;
    }

    /**
     * @return string|string[]
     */
    private function selected(): string|array
    {
        return self::selectedValues();
        // if ($this->type === SelectType::Checkbox) {
        //     if (is_array($this->selected)) {
        //         return $this->selected;
        //     }
        //     return [$this->selected];
        // }

        // if (is_array($this->selected) and count($this->selected) > 0) {
        //     return $this->selected[0];
        // }
        // return $this->selected;
    }

    /**
     * @return list<string>
     */
    private function selectedValues(): array
    {
        $values = is_array($this->selected)
            ? array_values($this->selected)
            : [$this->selected];

        return match ($this->type) {
            SelectType::Multiple, SelectType::Checkbox => $values,
            SelectType::Dropdown, SelectType::Radio => array_slice($values, 0, 1)
        };
    }

    private function isSelected(string $value): bool
    {
        return in_array($value, self::selectedValues(), true);
        // if (self::hasSelected() === false) {
        //     return false;
        // }

        // if (is_array(self::selected())) {
        //     return in_array($value, self::selected());
        // }
        // return $value === self::selected();
    }

    public function __toString(): string
    {
        $warning  = self::warningMessage($this->id . '-warning');
        $selected = self::selectedValues();

        return (string) match ($this->type) {
            SelectType::Dropdown,
            SelectType::Multiple => self::selectDropdown($warning, $selected),
            SelectType::Radio,
            SelectType::Checkbox => Self::selectGroup($warning, $selected)
        };
        // if ($this->type === SelectType::Dropdown) {
        //     return (string) self::selectDropdown();
        // }
        // return (string) self::selectOther();
    }

    private function selectDropdown(
        string|PropertyInterface $warning,
        array $selected
    ): Element {
        $options = [];

        foreach ($this->options as $value => $label) {
            $value = (string) $value;
            $option = Element::option($label)->props('value ' . $value);

            if (in_array($value, $selected, true)) {
                $option = $option->prop('selected selected');
            }

            $options[] = $option;
        }

        $inputProperties = self::controlProperties($warning);

        $select = Element::select(...$options)->props(...[
            ...$inputProperties,
            'id ' . $this->id,
            'name ' . $this->name
        ]);

        if ($this->type === SelectType::Multiple) {
            $select = $select->prop('multiple multiple');
        }

        return Element::div(
            Element::label($this->label)->props(...[
                ...$this->labelProperties,
                'for ' . $this->id
            ]),
            $select,
            ...[...$this->content, $warning]
        )->props(...$this->wrapperProperties);
        // $elements = [];

        // $warningMessage = self::warningMessage();
        // $hasWarningMessage = ($warningMessage !== '');

        // foreach ($this->options as $value => $content) {
        //     $value  = (string) $value;
        //     $option = Element::option($content)->props('value ' . $value);
        //     if (self::isSelected($value)) {
        //         $option = $option->prop('selected selected');
        //     }
        //     $elements[] = $option;
        // }

        // $input = Element::select(
        //     ...$elements
        // )->props('id ' . $this->name, 'name ' . $this->name);
        // if ($hasWarningMessage) {
        //     $input = $input->prop('aria-invalid true');
        //     $input = $input->prop('aria-describedby ' . $this->warningId);
        // }

        // return Element::div(
        //     Element::label(
        //         $this->label
        //     )->props('for ' . $this->name, ...$this->labelProperties),
        //     $input,
        //     $warningMessage
        // )->props(...$this->wrapperProperties);
    }

    /**
     * @param list<string> $selected
     */
    private function selectGroup(
        string|PropertyInterface $warning,
        array $selected
    ): Element {
        $members = [];
        $isCheckbox = $this->type === SelectType::Checkbox;
        $type = $isCheckbox ? 'checkbox' : 'radio';
        $name = (string) $this->name;

        // Retains existing checkbox-group naming without adding [] twice.
        if ($isCheckbox && str_ends_with($name, '[]') === false) {
            $name .= '[]';
        }

        $inputProperties = self::controlProperties($warning);
        $index = 0;

        foreach ($this->options as $value => $label) {
            $value = (string) $value;
            $memberId = $this->id . '-option-' . $index++;

            $input = Element::input()->omitEndTag()->props(...[
                ...$inputProperties,
                'id ' . $memberId,
                'name ' . $name,
                'type ' . $type,
                'value ' . $value
            ]);

            if (in_array($value, $selected, true)) {
                $input = $input->prop('checked checked');
            }

            $members[] = Element::div(
                $input,
                Element::label($label)->props('for ' . $memberId)
            );
        }

        return Element::fieldset(
            Element::legend($this->label)->props(...$this->labelProperties),
            ...[...$members, ...$this->content, $warning]
        )->props(...$this->wrapperProperties);
    }

    private function selectOther(): Fieldset
    {
        $elements = [];
        $type = $this->type === SelectType::Checkbox ? 'checkbox' : 'radio';

        $warningMessage = self::warningMessage();
        $hasWarningMessage = ($warningMessage !== '');

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

        return Fieldset::create(
            $this->label,
            $this->name,
            $warningMessage
        )->elements(...$elements)->labelProps(...$this->labelProperties);

        // return Element::fieldset(
        //     Element::legend($this->label)->props(...$this->labelProperties),
        //     ...$elements
        // );
    }

    /** @return list<string> */
    private function controlProperties(
        string|PropertyInterface $warning
    ): array {
        $properties = [];
        $descriptionIds = [];

        foreach ($this->inputProperties as $property) {
            if (str_starts_with($property, 'aria-describedby ')) {
                $ids = preg_split(
                    '/\s+/',
                    trim(substr($property, strlen('aria-describedby ')))
                );

                foreach ($ids === false ? [] : $ids as $id) {
                    if ($id !== '') {
                        $descriptionIds[] = $id;
                    }
                }
            } else {
                $properties[] = $property;
            }
        }

        if ($warning !== '') {
            $descriptionIds[] = $this->id . '-warning';
            $properties[] = 'aria-invalid true';
        }

        if ($descriptionIds !== []) {
            $properties[] = 'aria-describedby '
                . implode(' ', array_unique($descriptionIds));
        }

        return $properties;
    }
}
