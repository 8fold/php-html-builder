# Migrating from 5.0 to 5.1

While not a major release, it could have been, but the potential breaking change should only impact some static analysis tools - not runtimes - and has at least two solutions that have existed for multiple versions and many of us already have in place.

Otherwise, this is a quality of life improvement release.

- [New](#new)
    - [`EmptyElement`](#emptyelement) component
- [Updated](#updated)
    - (could cause frustration) [Static analysis](#static-analysis) may throw if the element name in an `Element` or `EmptyElement` static creation is not in the HTML specification, is not declared empty by the HTML specification, or both.
    - (see previous) Removed all [`ignoreErrors`](https://phpstan.org/user-guide/ignoring-errors#ignoring-in-configuration-file) from the PHPStan configuration.
    - (see previous) Increased the [PHPStan](https://phpstan.org) level for HTML Builder to 10.
    - Expanded style and static analysis coverage to include `tests/` and `bin/` directories; not just `src/`.
    - Form controls, `Favicons`, and `Document` use `EmptyElement` where applicable
- Deprecated: None
- Deleted: None

## New

### `EmptyElement`

The first quality of life improvement is introducing an explicit way to create empty elements.

The examples in the `EmptyElement` section in the [README](../README.md#emptyelement) demonstrates the improvement.

In short:

```php
use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\EmptyElement;

Element::img()->omitEndTag()->prop('id cat-picture', 'src /path/to/image.png');

// becomes
EmptyElement::img('id cat-picture', 'src /path/to/image.png');
```

Outputs:

```html
<img id="cat-picture" src="/path/to/image.png">
```

## Updated

### Static analysis

The static analysis error that’s been with PHP HTML Builder from the beginning was “Call to undefined static method”.

The error could be silenced, and limited to only ignoring PHP HTML Builder, in the PHPStan configuration (the first workaround):

```yaml
ignoreErrors:
    - '#Call to an undefined static method Eightfold\\HtmlBuilder\\Element::(.*)#'
```

We introduced the ‘create()’ named constructor, which gave a specific pattern for creating custom components and allowed users to avoid ignoring the static analysis error (the second workaround):

```php
Element::create('p', 'Hello, World!');
```

PHP HTML Builder 5.1 introduces two docblocks.

[The first](../docblocks/elements-generated.txt) lists all HTML elements in the living specification. [The second](../docblocks/empty-elements-generated.txt) lists all *empty* elements in the living specification. Both are derived from sources maintained by the W3C organization and community.

This means, when using the `__callStatic` magic method named constructor to create elements, if the element name is in the docblock list, static analyzers (specifically PHPStan) will *not* throw an error. Further, if the element name is not in the list, PHPStan *will* throw an error, which may be informative to users.

```php
// Works with PHPStan level 10 without extra setup
Element::p('Hello, World!');
```

Note: You can still create custom element names while using static analysis by either calling the `create()` method directly (recommended) or ignoring via configuration.

Using `create()` may enhance readability by distinguishing custom element names from those in the HTML spec.
