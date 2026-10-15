# 8fold HTML Builder for PHP

HTML Builder extends and is a web-specific implementation of
[8fold XML Builder](https://github.com/8fold/php-xml-builder); similar to how
HTML extends and is a specific implementation of XML.

## Installation

`composer require 8fold/php-html-builder`

## Usage (basic)

Warning: Users of this library are responsible for sanitizing content.

By default, HTML Builder can quickly output an HTML string with consistent
ordering of attributes for elements. The default order can be found in the
[`Element`](https://github.com/8fold/php-html-builder/blob/main/src/Element.php)
class, which can be overridden, if you prefer a different ordering. This helps
create and maintain a consistent HTML pattern. Further, this pattern can be
changed without updating templates or other code.

```php
use Eightfold\HTMLBuilder\Document;
use Eightfold\HTMLBuilder\Element;

Document::create('title')
  ->head(
    Element::link()
      ->omitEndTag()->props('rel stylesheet', 'href style.css'),
    Element::script()->props('src script.js')
  )->body(
    Element::p('paragraph content')
  )
```

Output:

```html
<!doctype html>
<html lang="en"><head><title>title</title><meta charset="utf-8"><link rel="stylesheet" href="style.css"><script src="script.js"></script></head><body><p>paragraph content</p></body></html>
```

Output (formatted):

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

Further examples can be found in the
[`tests`](https://github.com/8fold/php-html-builder/tree/main/tests) folder with
an example extension.

A note on properties: If a property is not in the ordered list, and the value of
the property is the same as the name of the property, HTML Builder will presume
it's a boolean value and will truncate the definition in the HTML output.

```php
Element::input()->omitEndTag()->props("required required");
```

Output:

```html
<input required>
```

## Form control guidance

`TextInput`, `EmailInput`, `TextArea`, and all `Select` modes preserve help IDs
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

### Migrating from wrapperProps

In 5.0, use `containerProps()` instead of `wrapperProps()` on all form
components. This is a rename, not a change in property handling or target
elements. `wrapperProps()` remains a forwarding alias throughout 5.x and is
marked with PHP's native `#[Deprecated]` attribute (supported since PHP 8.4).
Calling it emits `E_USER_DEPRECATED`; removal is deferred until at least 6.0.

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

## Details

See [8fold XML Builder](https://github.com/8fold/php-xml-builder#readme) for more
details.

## Other

- [Code of Conduct](https://github.com/8fold/php-html-builder/blob/master/.github/CODE_OF_CONDUCT.md)
- [Contributing](https://github.com/8fold/php-html-builder/blob/master/.github/CONTRIBUTING.md)
- [Governance](https://github.com/8fold/php-html-builder/blob/master/.github/GOVERNANCE.md)
- [Versioning](https://github.com/8fold/php-html-builder/blob/master/.github/VERSIONING.md)
- [Security](https://github.com/8fold/php-html-builder/blob/master/.github/SECURITY.md)
