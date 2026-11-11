<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder;

use Eightfold\HtmlBuilder\PropertyInterface;

use Eightfold\HtmlBuilder\Element;

use Stringable;

/**
 * Magic static void-element factories. Generated from the WHATWG HTML
 * living standard (https://html.spec.whatwg.org/, retrieved 2026-11-10):
 * 13 void elements, derived from each element's "Tag omission in
 * text/html: No end tag." entry. Alphabetical.
 *
 * @method static static area(string ...$properties)
 * @method static static base(string ...$properties)
 * @method static static br(string ...$properties)
 * @method static static col(string ...$properties)
 * @method static static embed(string ...$properties)
 * @method static static hr(string ...$properties)
 * @method static static img(string ...$properties)
 * @method static static input(string ...$properties)
 * @method static static link(string ...$properties)
 * @method static static meta(string ...$properties)
 * @method static static source(string ...$properties)
 * @method static static track(string ...$properties)
 * @method static static wbr(string ...$properties)
 */
class EmptyElement implements PropertyInterface
{
    public static function create(string $name, string ...$properties): static
    {
        $properties = array_values($properties);
        return new static($name, $properties);
    }

    /**
     * @param  list<string>  $properties
     */
    public static function __callStatic(string $name, array $properties = []): static
    {
        return new static($name, $properties);
    }

    /**
     * @param  list<string>  $properties
     */
    final private function __construct(
        private string $name,
        private array $properties = [],
    ) {
    }

    public function props(string ...$properties): static
    {
        $this->properties = array_values($properties);
        return $this;
    }

    public function prop(string $prop): static
    {
        $this->properties[] = $prop;
        return $this;
    }

    public function toString(): string
    {
        return (string) Element::create($this->name)->omitEndTag()
            ->props(...$this->properties);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
