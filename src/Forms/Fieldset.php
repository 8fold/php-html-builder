<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\PropertyInterface;

use Eightfold\HtmlBuilder\Forms\SelectType;

class Fieldset implements Stringable
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

    /**
     * @var array<string|Stringable>
     */
    private array $elements = [];

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

    public function elements(string|Stringable ...$elements): self
    {
        $this->elements = $elements;
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
        $elements = $this->elements;
        $elements[] = self::warningMessage();

        return (string) Element::fieldset(
            Element::legend($this->label)->props(...$this->labelProperties),
            ...$elements
        )->props(...$this->wrapperProperties);
    }
}
