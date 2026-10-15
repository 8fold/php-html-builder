<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder;

use Stringable;

interface PropertyInterface extends Stringable
{
    public function props(string ...$properties): static;

    public function prop(string $prop): static;
}
