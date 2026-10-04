<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder;

interface PropertyInterface
{
    public function props(string ...$properties): static;

    public function prop(string $prop): static;
}
