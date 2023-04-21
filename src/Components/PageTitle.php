<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Components;

use Stringable;

use Eightfold\HTMLBuilder\Element;

class PageTitle implements Stringable
{
    public static function create(array $titles, string $separator = ' | '): self
    {
        return new self($title, $separator);
    }

    final private function __construct(
        private array $titles,
        private readonly string $separator
    ) {
    }

    public function __toString(): string
    {
        return (string) Element::title(
            implode($separator, $titles)
        );
    }
}
