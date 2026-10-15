<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\PropertyInterface;

use Eightfold\HtmlBuilder\Forms\ControlProperties;
use Eightfold\HtmlBuilder\Forms\SelectType;
use Eightfold\HtmlBuilder\Forms\Fieldset;

class Select implements Stringable
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
        $properties = array_values(array_filter(
            $this->inputProperties,
            static fn(string $property): bool => !in_array(
                explode(' ', $property, 2)[0],
                ['checked', 'multiple'],
                true
            )
        ));

        return ($noWarning)
            ? $properties
            : ControlProperties::withWarning(
                $properties,
                $this->id . '-warning'
            );
    }

    public function __toString(): string
    {
        $warning         = self::warningMessage();
        $inputProperties = self::inputProperties($warning === '');
        $selected        = self::selectedValues();

        return (string) match ($this->type) {
            SelectType::Dropdown,
            SelectType::Multiple => self::selectDropdown(
                $warning,
                $inputProperties,
                $selected
            ),
            SelectType::Radio,
            SelectType::Checkbox => self::selectGroup(
                $warning,
                $inputProperties,
                $selected
            )
        };
    }

    /**
     * @param list<string> $inputProperties
     * @param list<string> $selected
     */
    private function selectDropdown(
        string $warning,
        array $inputProperties,
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
    }

    /**
     * @param list<string> $inputProperties
     * @param list<string> $selected
     */
    private function selectGroup(
        string $warning,
        array $inputProperties,
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
}
