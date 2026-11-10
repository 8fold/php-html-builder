<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder;

use Eightfold\HtmlBuilder\PropertyInterface;

use Eightfold\HtmlBuilder\Element;

use Stringable;

/**
 * Magic static element factories. Generated from w3c/elements-of-html
 * elements.json (retrieved 2026-11-10): 154 entries, 132 unique elements after
 * collapsing the 22 input[type=...] variants into input. Sectioned by
 * era: current elements first, then deprecated groups by when they were
 * dropped, each group alphabetical.
 *
 * Element semantics per the WHATWG HTML living standard:
 * https://html.spec.whatwg.org
 *
 * Available in 5.2 (current):
 * @method static static a(string|\Stringable ...$content)
 * @method static static abbr(string|\Stringable ...$content)
 * @method static static address(string|\Stringable ...$content)
 * @method static static area(string|\Stringable ...$content)
 * @method static static article(string|\Stringable ...$content)
 * @method static static aside(string|\Stringable ...$content)
 * @method static static audio(string|\Stringable ...$content)
 * @method static static b(string|\Stringable ...$content)
 * @method static static base(string|\Stringable ...$content)
 * @method static static bdi(string|\Stringable ...$content)
 * @method static static bdo(string|\Stringable ...$content)
 * @method static static blockquote(string|\Stringable ...$content)
 * @method static static body(string|\Stringable ...$content)
 * @method static static br(string|\Stringable ...$content)
 * @method static static button(string|\Stringable ...$content)
 * @method static static canvas(string|\Stringable ...$content)
 * @method static static caption(string|\Stringable ...$content)
 * @method static static cite(string|\Stringable ...$content)
 * @method static static code(string|\Stringable ...$content)
 * @method static static col(string|\Stringable ...$content)
 * @method static static colgroup(string|\Stringable ...$content)
 * @method static static data(string|\Stringable ...$content)
 * @method static static datalist(string|\Stringable ...$content)
 * @method static static dd(string|\Stringable ...$content)
 * @method static static del(string|\Stringable ...$content)
 * @method static static details(string|\Stringable ...$content)
 * @method static static dfn(string|\Stringable ...$content)
 * @method static static dialog(string|\Stringable ...$content)
 * @method static static div(string|\Stringable ...$content)
 * @method static static dl(string|\Stringable ...$content)
 * @method static static dt(string|\Stringable ...$content)
 * @method static static em(string|\Stringable ...$content)
 * @method static static embed(string|\Stringable ...$content)
 * @method static static fieldset(string|\Stringable ...$content)
 * @method static static figcaption(string|\Stringable ...$content)
 * @method static static figure(string|\Stringable ...$content)
 * @method static static footer(string|\Stringable ...$content)
 * @method static static form(string|\Stringable ...$content)
 * @method static static h1(string|\Stringable ...$content)
 * @method static static h2(string|\Stringable ...$content)
 * @method static static h3(string|\Stringable ...$content)
 * @method static static h4(string|\Stringable ...$content)
 * @method static static h5(string|\Stringable ...$content)
 * @method static static h6(string|\Stringable ...$content)
 * @method static static head(string|\Stringable ...$content)
 * @method static static header(string|\Stringable ...$content)
 * @method static static hr(string|\Stringable ...$content)
 * @method static static html(string|\Stringable ...$content)
 * @method static static i(string|\Stringable ...$content)
 * @method static static iframe(string|\Stringable ...$content)
 * @method static static img(string|\Stringable ...$content)
 * @method static static input(string|\Stringable ...$content)
 * @method static static ins(string|\Stringable ...$content)
 * @method static static kbd(string|\Stringable ...$content)
 * @method static static keygen(string|\Stringable ...$content)
 * @method static static label(string|\Stringable ...$content)
 * @method static static legend(string|\Stringable ...$content)
 * @method static static li(string|\Stringable ...$content)
 * @method static static link(string|\Stringable ...$content)
 * @method static static main(string|\Stringable ...$content)
 * @method static static map(string|\Stringable ...$content)
 * @method static static mark(string|\Stringable ...$content)
 * @method static static menu(string|\Stringable ...$content)
 * @method static static menuitem(string|\Stringable ...$content)
 * @method static static meta(string|\Stringable ...$content)
 * @method static static meter(string|\Stringable ...$content)
 * @method static static nav(string|\Stringable ...$content)
 * @method static static noscript(string|\Stringable ...$content)
 * @method static static object(string|\Stringable ...$content)
 * @method static static ol(string|\Stringable ...$content)
 * @method static static optgroup(string|\Stringable ...$content)
 * @method static static option(string|\Stringable ...$content)
 * @method static static output(string|\Stringable ...$content)
 * @method static static p(string|\Stringable ...$content)
 * @method static static param(string|\Stringable ...$content)
 * @method static static picture(string|\Stringable ...$content)
 * @method static static pre(string|\Stringable ...$content)
 * @method static static progress(string|\Stringable ...$content)
 * @method static static q(string|\Stringable ...$content)
 * @method static static rb(string|\Stringable ...$content)
 * @method static static rp(string|\Stringable ...$content)
 * @method static static rt(string|\Stringable ...$content)
 * @method static static ruby(string|\Stringable ...$content)
 * @method static static s(string|\Stringable ...$content)
 * @method static static samp(string|\Stringable ...$content)
 * @method static static script(string|\Stringable ...$content)
 * @method static static section(string|\Stringable ...$content)
 * @method static static select(string|\Stringable ...$content)
 * @method static static small(string|\Stringable ...$content)
 * @method static static source(string|\Stringable ...$content)
 * @method static static span(string|\Stringable ...$content)
 * @method static static strong(string|\Stringable ...$content)
 * @method static static style(string|\Stringable ...$content)
 * @method static static sub(string|\Stringable ...$content)
 * @method static static summary(string|\Stringable ...$content)
 * @method static static sup(string|\Stringable ...$content)
 * @method static static table(string|\Stringable ...$content)
 * @method static static tbody(string|\Stringable ...$content)
 * @method static static td(string|\Stringable ...$content)
 * @method static static template(string|\Stringable ...$content)
 * @method static static textarea(string|\Stringable ...$content)
 * @method static static tfoot(string|\Stringable ...$content)
 * @method static static th(string|\Stringable ...$content)
 * @method static static thead(string|\Stringable ...$content)
 * @method static static time(string|\Stringable ...$content)
 * @method static static title(string|\Stringable ...$content)
 * @method static static tr(string|\Stringable ...$content)
 * @method static static track(string|\Stringable ...$content)
 * @method static static u(string|\Stringable ...$content)
 * @method static static ul(string|\Stringable ...$content)
 * @method static static var(string|\Stringable ...$content)
 * @method static static video(string|\Stringable ...$content)
 * @method static static wbr(string|\Stringable ...$content)
 *
 * Deprecated in 5; last available in 4.01, X1.1:
 * @method static static acronym(string|\Stringable ...$content)
 * @method static static big(string|\Stringable ...$content)
 * @method static static tt(string|\Stringable ...$content)
 *
 * Deprecated in X1.1; last available in 4.01, X1.0:
 * @method static static applet(string|\Stringable ...$content)
 * @method static static basefont(string|\Stringable ...$content)
 * @method static static center(string|\Stringable ...$content)
 * @method static static dir(string|\Stringable ...$content)
 * @method static static font(string|\Stringable ...$content)
 * @method static static isindex(string|\Stringable ...$content)
 * @method static static noframes(string|\Stringable ...$content)
 * @method static static strike(string|\Stringable ...$content)
 *
 * Deprecated in X1.0; last available in 4.01:
 * @method static static frame(string|\Stringable ...$content)
 * @method static static frameset(string|\Stringable ...$content)
 *
 * Deprecated in 4.01; last available in 3.2:
 * @method static static plaintext(string|\Stringable ...$content)
 * @method static static xmp(string|\Stringable ...$content)
 *
 * Deprecated in 3.2; last available in 2.0:
 * @method static static nextid(string|\Stringable ...$content)
 *
 * No spec data in source (verify before relying on these):
 * @method static static hgroup(string|\Stringable ...$content)
 * @method static static rbc(string|\Stringable ...$content)
 * @method static static rtc(string|\Stringable ...$content)
 */
class EmptyElement implements PropertyInterface
{
    /**
     * The following attributes will be placed in this order if used in an
     * element. To change the order, extend the Element class and override
     * this constant.
     */
    public const ORDERED = [
        "is",
        "role",
        "id",
        "class",
        "style",
        "name",
        "type",
        "media",
        "tabindex",
        "accesskey",
        "width",
        "height",
        "lang",
        "srclang",
        "hreflang",
        "dir",
        "translate",
        "src",
        "rel",
        "href",
        "target",
        "itemtype",
        "itemref",
        "itemprop",
        "title",
        "http-equiv",
        "charset",
        "alt",
        "value",
        "content",
        "manifest",
        "contenteditable",
        "spellcheck",
        "start"
    ];

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
