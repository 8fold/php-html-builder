# 8fold HTML Builder for PHP

HTML Builder is a web-specific string compiler.

Warning: Users of this library are responsible for sanitizing content.

- [Installation](#installation)

## Installation

`composer require 8fold/php-html-builder`

## Usage

HTML Builder quickly outputs HTML strings with consistent
ordering of attributes for elements.

The default order is in the [`Element`](https://github.com/8fold/php-html-builder/blob/main/src/Element.php)
class. If you prefer a different ordering, you can extend and override the ‘Element’ class and ordering constant.

### `Element`

The fundamental building block for all output.

```php
use Eightfold\HtmlBuilder\Element;

// non-empty element, using `create()`
Element::create('p', 'Hello, World!')->props('id intro');

// output
<p id="intro">Hello, World!</p>

// non-empty element, magic method
Element::p('Hello, World!')->props('id intro');

// output
<p id="intro">Hello, World!</p>

// empty element
Element::img()->omitEndTag()->props('src /path/to/image.png');

// output
<img src="/path/to/image.php">
```

### `EmptyElement`

The [`EmptyElement`](https://github.com/8fold/php-html-builder/blob/main/src/EmptyElement.php) class was introduced in the 5.1 release and is designed to mirror the input shorthand of HTML those elements compared to using the `Element` class to do the same.

```php
use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\EmptyElement;

// empty element use Element
Element::img()->omitEndTag()->props('src /path/to/image.png');

// output
<img src="/path/to/image.php">

// using EmptyElement
EmptyElement::img('src /path/to/image.png');

// output same as previous
```

### `Document`

The [`Document`](https://github.com/8fold/php-html-builder/blob/main/src/Document.php) class gives you to generate a complete HTML document entirely in PHP.

```
use Eightfold\HtmlBuilder\Document;
use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\EmptyElement;

Document::create('title')
  ->head(
    EmptyElement::link('rel stylesheet', 'href style.css'),
    Element::script()->props('src script.js')
  )->body(
    Element::p('Hello, World!')
  );

// Output (compressed):

```html
<!doctype html>
<html lang="en"><head><title>title</title><meta charset="utf-8"><link rel="stylesheet" href="style.css"><script src="script.js"></script></head><body><p>Hello, World!</p></body></html>

// Output (human-readable):

```html
<!doctype html>
<html lang="en">
  <head>
    <title>title</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
  </head>
  <body>
    <p>paragraph content</p>
  </body>
</html>
```

Further examples are in the [`tests`](https://github.com/8fold/php-html-builder/tree/main/tests) folder with an example extension.

### `Comment`

There’s even a [`Comment`](https://github.com/8fold/php-html-builder/blob/main/src/Comment.php) element for adding inline comments to the HTML.

```
use Eightfold\HtmlBuilder\Document;
use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\EmptyElement;
use Eightfold\HtmlBuilder\Comment;

Document::create('title')
  ->head(
    EmptyElement::link('rel stylesheet', 'href style.css'),
    Element::script()->props('src script.js')
  )->body(
    Comment::create('comment-based-injection-point'),
    Element::p('Hello, World!')
  );
  
// Output (human-readable):

```html
<!doctype html>
<html lang="en">
  <head>
    <title>title</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
  </head>
  <body>
    <!-- comment-based-injection-point -->
    <p>paragraph content</p>
  </body>
</html>
```

### Properties

`Element` arranges all properties that are not in the ordered list alphabetically after the ordered properties.

Properties with same name and value are presumed to be boolean properties (`required`), and will placed at the end of the element.

```php
use Eightfold\HtmlBuilder\EmptyElement;

EmptyElement::input('required required');

// Output:

```html
<input required>
```

You may also add properties individually to facilitate dynamic construction.

```php
use Eightfold\HtmlBuild\Element;

$callout = Element::div('Information in the callout.')
    ->props('class callout', 'aria-role alert');
    
if ($isWarning) {
    $callout = $callout->prop('is warning');
}

// output (human-readable): $isWarning = false
<div class="callout" aria-role="alert">
    Information in the callout.
</div>

// output (human-readable): $isWarning = true
<div is="warning" class="callout" aria-role="alert">
    Information in the callout.
</div>
```

## Extending

Each HTML Builder component accepts string literals and classes that implement the PHP-native `Stringable` interface. Further, HTML Build provides the [`PropertyInterface`](https://github.com/8fold/php-html-builder/blob/main/src/PropertyInterface.php), which extends the `Stringable` interface. Therefore, HTML Builder allows developers to create custom components and APIs without extending HTML Builder’s base components.

## Performance

The `/comparisons` folder can be used to run performance tests locally to compare the same output using:

1. straight HTML,
2. standard PHP,
3. PHP HTML Builder alongside standard PHP (the Element class),
4. PHP HTML Builder only (the Document class), and
5. PHP HTML Builder only using PSR-4 autoloading.

These results are from August 22nd, 2026 (running on PHP 8.5):

|Configuration |Average time in milliseconds |Median time in milliseconds |Transfer size in kilobytes |
|:---|---:|---:|---:|
|1 |0.0022 |0.0020 |2.7  |
|2 |0.0151 |0.0125 |1.99 |
|3 |0.4314 |0.3025 |1.82 |
|4 |1.4446 |0.3672 |1.74 |
|5 |0.9059 |0.6750 |1.74 |

These results are from February 26th, 2024:

|Configuration |Average time in milliseconds |Median time in milliseconds |Transfer size in kilobytes |
|:---|---:|---:|---:|
|1 |0.0399683 |0.0017415 |2.7  |
|2 |0.0953241 |0.0160480 |1.99 |
|3 |1.6026525 |1.4603320 |1.82 |
|4 |1.7849769 |1.7918970 |1.74 |
|5 |8.9957415 |8.8755520 |1.74 |

Things worth noting (as rough interpretations):

1. PHP being used as a template engine has a negligible impact on speed.
2. Using `require_once` without PSR-4 autoloading decreases speed by roughly 1.5 milliseconds; however, once the code is made available, very little additional cost seems to be present.
3. Using PSR-4 decreases speed by roughly 6 milliseconds; however, ensures everything is present and available.
4. Using PHP HTML Builder Document and Element resulted in 1 kilobyte reduction in packet size. (Increased processing time in milliseconds most likely offset by decreased download time.)

## Migrating from 5.0 to 5.1

While not a major release, it could have been, but the potential breaking change should only impact some static analysis tools - not runtimes - and has at least two solutions that have existed for multiple versions and many of us already have in place.

Otherwise, this is a quality of life improvement release.

### `EmptyElement`

The first quality of life improvement is introducing an explicit way to create empty elements.

The examples in the `EmptyElement` section above demonstrate the improvement.

### Static analysis and element names

- We expanded style and static analysis coverage to the `src/`, `tests/`, and `bin/` directories. 
- We increased the [PHPStan](https://phpstan.org) level for HTML Builder to 10. 
- We removed all [`ignoreErrors`](https://phpstan.org/user-guide/ignoring-errors#ignoring-in-configuration-file) from the PHPStan configuration.

The valid PHPStan error that’s been with HTML Builder from the beginning was “Call to undefined static method”.

The error could be silenced, and limited to only ignoring HTML Builder, in the PHPStan configuration (the first workaround):

```
	ignoreErrors:
		- '#Call to an undefined static method Eightfold\\HtmlBuilder\\Element::(.*)#'
```

We introduced the ‘create()’ named constructor to help those who did not want to ignore the error in their configurations as well (the second workaround):

```
Element::create('p', 'Hello, World!');
```

The 5.1 release introduces a docblock, listing all HTML element names in the living specification; derived from sources maintained by the W3C organization and community. This means, as long as the element being created by the `__callStatic` magic method named constructor is in the docblock list, PHPStan will *not* throw an error. Further, if the element name is not in the list, PHPStan *will* throw an error, which may be informative to users.

```
// Works with PHPStan level 10 without extra setup
Element::p('Hello, World!');
```

Note: You can still create custom element names while using static analysis by either calling the `create()` method (recommended) or ignoring via configuration. Using `create()` may enhance readability by distinguishing custom element names from those in the HTML spec.

## Migrating from 4.x to 5.0

This section highlights the changes to check when upgrading from 4.4.0. It links to the [form control guidance](#form-control-guidance) for details that apply to the new and existing APIs.

### Required migration checks: markup and property targets

Text-entry controls now render an outer `<div>` unconditionally. In 4.4.0,
`TextInput`, `EmailInput`, and `Password` rendered their label and input as siblings without
that container. Update CSS child/sibling selectors, flex or grid layouts, and
JavaScript traversal that assumed the old structure.

```html
<!-- 4.4.0 -->
<label for="email">Email</label><input id="email" name="email" type="text">

<!-- 5.0 -->
<div class="field">
  <label for="email">Email</label><input id="email" name="email" type="text">
</div>
```

Use each property method for its element: `containerProps()` targets the outer
container, `labelProps()` targets the label (or a Select group's legend), and
`inputProps()` targets the input, textarea, or select. Fieldset's `labelProps()`
targets its legend. For example:

```php
use Eightfold\HtmlBuilder\Forms\TextInput;

TextInput::create('Email', 'email')
    ->containerProps('class field')
    ->labelProps('class field-label')
    ->inputProps('class form-control');
```

Do not use `inputProps()` or `labelProps()` to replace generated properties
such as `type`, `id`, `name`, `value`, or the label's `for` association. For
example, 4.4.0 allowed `TextInput::create('Email', 'email')->inputProps('type email')`
to change its generated type; in 5.0, use `EmailInput` instead. Use constructor
arguments, `id()`, or the component/mode that matches the control you need.
Setting a custom ID changes the DOM ID and its generated label and warning
associations, but does not change the submitted field name:

```php
TextInput::create('Email', 'account_email')->id('email-field');
// The input has id="email-field" and name="account_email".
```

### Choose Select modes and names explicitly

The default remains a single dropdown. `multiple()`, `radio()`, and
`checkbox()` explicitly choose the other modes. The initial selection is
single-valued for dropdowns and radio groups (only the first supplied value is
considered), and every matching value is selected for multiple dropdowns and
checkbox groups.

Dropdowns, including multiple dropdowns, and radio groups submit the supplied
name unchanged. Include `[]` yourself when a multiple dropdown should submit a
PHP array. Checkbox groups continue to append `[]` unless the supplied name
already ends with it. `checked` and `multiple` passed through `inputProps()` are
ignored; use the selection argument and mode methods instead. See
[Select attributes and selection](#select-attributes-and-selection) for native
attribute targets and examples.

Radio and checkbox member IDs now use `<control-id>-option-<zero-based-index>`
instead of `<name>-<option-value>`. For example, option `a` in a group named
`categories` changes from `categories-a` to `categories-option-0`. Generated
labels follow the new IDs automatically; update external labels, CSS selectors,
and JavaScript that reference the old IDs. `id()` changes the group's member-ID
prefix, not its submitted name.

### Update custom PropertyInterface implementations

`PropertyInterface` now extends PHP's `Stringable`. Any custom implementation
must add `public function __toString(): string` alongside `prop()` and
`props()`.

### Additive APIs and deprecated, still-working calls

`containerProps()` is available for form controls, and `id()` is available on
TextInput, EmailInput, Password, TextArea, and Select. Select also adds
`multiple()` and `inputProps()`. These APIs are additive.

`wrapperProps()` remains callable as a forwarding alias throughout 5.x. It
continues to set the same container properties, but is deprecated in favor of
`containerProps()` and emits `E_USER_DEPRECATED`. The native `#[Deprecated]`
attribute is supported by PHP 8.4 and later; removal is deferred until at
least 6.0. See [Migrating from wrapperProps](#migrating-from-wrapperprops).

Escaping remains caller-owned; this upgrade does not introduce automatic
escaping. `TextArea` is a new control, and its caller-supplied, already-escaped
value is rendered as text content. Its leading-newline and newline-normalizing
behavior is described in [Textarea values](#textarea-values).

## Form control guidance

`TextInput`, `EmailInput`, `Password`, `TextArea`, and all `Select` modes preserve help IDs
supplied through `inputProps('aria-describedby help-id')` when adding a warning.
With a warning, the control sets `aria-invalid="true"` and appends
`<control-id>-warning` to `aria-describedby`, removing duplicate references while
preserving their order. Use `content()` to supply the referenced help elements.
Overriding the control's `id()` also changes its generated warning ID.

`tests/ControlGuidanceTest.php` checks these relationships across control types.
Assertion failures identify the relationship, control type and ID, and rendered
HTML, alongside PHPUnit's named case and expected/actual diff.

### Text and email input values

`TextInput` and `EmailInput` use the constructor's initial value, including an
empty string. Caller-supplied `value` properties in `inputProps()` are ignored;
an empty constructor value omits the attribute rather than retaining stale
caller content. Unrelated native input properties are preserved. Values remain
caller-sanitized: the components do not escape or encode them.

Text-entry controls and dropdown selects keep each label's generated `for`
association in sync with the control's `id()`, overriding conflicting
`labelProps('for other')` properties while preserving unrelated label properties.
For radio and checkbox groups, `labelProps()` targets the legend instead.

### Textarea values

`TextArea::create($label, $name, $value = '', $warningMessage = '')` accepts
**caller-sanitized content** for `$value`, consistent with the other controls.
It renders the supplied value without escaping, decoding, or double encoding.
The implementer must escape untrusted text before passing it to the component;
an unescaped `</textarea>` can close the control and introduce markup.
Escaping flags, character encoding, and invalid-character handling remain the
implementer's choice.

```php
use Eightfold\HtmlBuilder\Forms\TextArea;

$value = "\nFirst line & second line\n";
$escapedValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

TextArea::create('Notes', 'notes', $escapedValue, 'Add more detail.')
    ->inputProps('rows 4', 'required required')
    ->labelProps('class notes-label');
```

Values are element text, not a `value` attribute. Spaces, tabs, and leading,
internal, and trailing LF newlines are preserved: rendering adds one extra
leading newline that HTML consumes. Native HTML parsing normalizes CR and CRLF
line endings to LF; browsers apply their native textarea submission rules.
`inputProps()` targets the textarea and supports native `rows`, `required`,
`readonly`, and `disabled` attributes. Generated `id` and `name` override
conflicting input properties; `id()` also updates the label and warning
associations. String and `PropertyInterface` warnings follow the shared guidance
rules above.

The escaping call above is an application example, not a component default.
To display literal entity syntax such as `&amp;`, escape that text too (for
example, supply `&amp;amp;`). Callers remain responsible for sanitizing all
values, labels, warning markup, additional content, and attribute values.

### Select attributes and selection

`Select::inputProps()` targets the `<select>` in dropdown modes and **every**
member `<input>` in radio/checkbox modes. Use native attributes appropriate to
those elements: for example, `required`, `disabled`, and help references on
either, and `size` on a dropdown. Selects, radios, and checkboxes do not support
`readonly`; the component does not emulate it.

```php
use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\Forms\Select;

Select::create('Category', 'category', ['a' => 'A', 'b' => 'B'], 'b')
    ->inputProps('required required', 'disabled disabled');

Select::create('Categories', 'categories[]', ['a' => 'A', 'b' => 'B'], ['a', 'b'])
    ->multiple()
    ->inputProps('size 4', 'aria-describedby categories-help')
    ->content(Element::p('Choose one or more.')->props('id categories-help'));
```

The default mode is a single dropdown. `dropdown()` and `radio()` consider only
the first supplied selection; `multiple()` and `checkbox()` select every matching
option. Unmatched values select nothing in the generated markup (a native
single dropdown may still display its first option by default). Option matching
uses strings, including for numeric option keys.

Dropdowns (including multiple dropdowns) and radio groups preserve the supplied
field name exactly. For PHP array submissions, supply a name such as
`categories[]` yourself. Checkbox groups retain the existing convention of
appending `[]` unless the name already ends with it.

`containerProps()` targets the dropdown's `<div>` or the group's `<fieldset>`.
For native group-wide disabling, use
`->checkbox()->containerProps('disabled disabled')` (or `radio()`); the browser
disables descendants through fieldset semantics, without rewriting the inputs.
Disabling a dropdown instead requires `inputProps('disabled disabled')`.
`labelProps()` targets the dropdown's label or the group's legend.

`inputProps('required required')` on a radio group uses native radio-group
validation. On a checkbox group it makes **every checkbox required**, not
"at least one checkbox"; the component provides no generic group-required rule.

Generated control IDs and submitted names take precedence over `inputProps()`;
group input types and option values are also generated. `checked` and `multiple`
properties supplied through `inputProps()` are ignored: use the `selected`
argument and explicit mode methods instead. A warning forces
`aria-invalid="true"` and merges help references as described above. Without a
warning, caller-supplied guidance and invalid-state properties remain unchanged.
Other repeated properties use the element builder's normal last-value behavior.

### Fieldset warnings

`Fieldset::create($label, $name, $warningMessage = '')` renders one group warning
with the ID `<name>-warning`. String warnings are wrapped in a paragraph and
retain caller-supplied markup. `PropertyInterface` warnings retain their element,
content, and other properties; the generated ID is applied to a local clone,
leaving the caller's warning object unchanged. Repeated rendering is stable, and
the same warning object can be reused across components with independent IDs.

Fieldset does not inspect or rewrite arbitrary children passed to `elements()`.
Callers remain responsible for setting `aria-invalid` and `aria-describedby` on
member controls, including references to the group warning when appropriate.

### Migrating from wrapperProps

In 5.0, use `containerProps()` instead of `wrapperProps()` on all form
components. This is a rename, not a change in property handling or target
elements. `wrapperProps()` remains a forwarding alias throughout 5.x and is
marked with PHP's native `#[Deprecated]` attribute (supported since PHP 8.4).
Calling it emits `E_USER_DEPRECATED`; removal is deferred until at least 6.0.

## Details

See [8fold XML Builder](https://github.com/8fold/php-xml-builder#readme) for more
details.

## Other

- [Code of Conduct](https://github.com/8fold/php-html-builder/blob/master/.github/CODE_OF_CONDUCT.md)
- [Contributing](https://github.com/8fold/php-html-builder/blob/master/.github/CONTRIBUTING.md)
- [Governance](https://github.com/8fold/php-html-builder/blob/master/.github/GOVERNANCE.md)
- [Versioning](https://github.com/8fold/php-html-builder/blob/master/.github/VERSIONING.md)
- [Security](https://github.com/8fold/php-html-builder/blob/master/.github/SECURITY.md)
