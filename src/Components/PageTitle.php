<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Components;

use Stringable;

use Eightfold\HTMLBuilder\Element;

class PageTitle implements Stringable
{
    /**
     * @param string[] $titles
     */
    public static function create(array $titles, string $separator = ' | '): self
    {
        return new self($titles, $separator);
    }

    /**
     * @param string[] $titles
     */
    final private function __construct(
        private array $titles,
        private readonly string $separator
    ) {
    }

    public function __toString(): string
    {
        return (string) Element::title(
            implode($this->separator, $this->titles)
        );
    }
}
