<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\PropertyInterface;

use Eightfold\HtmlBuilder\Forms\SelectType;

class Fieldset implements Stringable
{
    private string $warningId;

    /**
     * @var string[]
     */
    private array $containerProperties = [];

    /**
     * @var string[]
     */
    private array $labelProperties = [];

    /**
     * @var array<string|Stringable>
     */
    private array $elements = [];

    /**
     * Renders one group warning; callers must wire aria-invalid and
     * aria-describedby on member controls (see Select's radio/checkbox modes).
     */
    public static function create(
        string|Stringable $label,
        string|Stringable $name,
        string|PropertyInterface $warningMessage = ''
    ): self {
        return new self($label, $name, $warningMessage);
    }

    final private function __construct(
        private string|Stringable $label,
        private string|Stringable $name,
        private string|PropertyInterface $warningMessage = ''
    ) {
        $this->warningId = $this->name . '-warning';
    }

    public function elements(string|Stringable ...$elements): self
    {
        $this->elements = $elements;
        return $this;
    }

    /**
     * Sets properties on this component's outermost element: the fieldset.
     */
    public function containerProps(string ...$properties): self
    {
        $this->containerProperties = $properties;
        return $this;
    }

    #[\Deprecated(message: 'Use containerProps() instead.', since: '5.0')]
    public function wrapperProps(string ...$properties): self
    {
        return $this->containerProps(...$properties);
    }

    public function labelProps(string ...$properties): self
    {
        $this->labelProperties = $properties;
        return $this;
    }

    private function warningMessage(): string
    {
        if ($this->warningMessage === '') {
            return '';
        }

        if (is_string($this->warningMessage)) {
            return (string) Element::p($this->warningMessage)->props(
                'id ' . $this->warningId
            );
        }

        $warning = clone $this->warningMessage;

        return (string) $warning->prop('id ' . $this->warningId);
    }

    public function __toString(): string
    {
        $elements = $this->elements;
        $elements[] = self::warningMessage();

        return (string) Element::fieldset(
            Element::legend($this->label)->props(...$this->labelProperties),
            ...$elements
        )->props(...$this->containerProperties);
    }
}
