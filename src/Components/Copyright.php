<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Components;

use Eightfold\XMLBuilder\Contracts\Buildable;

use Eightfold\XMLBuilder\Implementations\Buildable as BuildableImp;

use Eightfold\HTMLBuilder\Element;

class Copyright implements Buildable
{
    use BuildableImp;

    private $useCopyrightSymbol = true;

    private $spellOutCopyright = false;

    private $scope = '';

    public static function create(
        string $holder,
        string $startYear = '',
        bool $wrapInParagraph = true
    ): self {
        return new self($holder, $startYear, $wrapInParagraph);
    }

    final private function __construct(
        private string $holder,
        private string $startYear,
        private bool $wrapInParagraph
    ) {
    }

    public function stringOnly(): self
    {
        $this->wrapInParagraph = false;
        return $this;
    }

    public function spellOutCopyright(): self
    {
        $this->spellOutCopyright = true;
        $this->useCopyrightSymbol = false;
        return $this;
    }

    public function expandedCopyright(): self
    {
        $this->spellOutCopyright = true;
        $this->useCopyrightSymbol = true;
        return $this;
    }

    public function allRightsReserved(): self
    {
        $this->scope = 'All rights reserved.';
        return $this;
    }

    public function noRightsReserved(): self
    {
        $this->scope = 'No rights reserved.';
        return $this;
    }

    public function someRightsReserved(): self
    {
        $this->scope = 'Some rights reserved.';
        return $this;
    }

    public function __toString(): string
    {
        $lead = [];
        if ($this->spellOutCopyright) {
            $lead[] = 'Copyright';
        }

        if ($this->useCopyrightSymbol) {
            $lead[] = '©';
        }

        $c = implode(' ', $lead);

        $time = date('Y');
        if (strlen($this->startYear) > 0) {
            $time = $this->startYear . '–' . $time;
        }

        $stringOnly = $c . ' ' . $time . ' ' . $this->holder;

        if (strlen($this->scope) > 0) {
            $stringOnly .= '. ' . $this->scope;
        }

        if ($this->wrapInParagraph) {
            return (string) Element::p($stringOnly);
        }
        return $stringOnly;
    }
}
