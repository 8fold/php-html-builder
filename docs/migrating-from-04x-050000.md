# Migrating from 4.x to 5.0

- New
    - [`TextArea`](#textarea) form control
    - [`Select`](#select) adds `multiple()` mode and `inputProps()` methods; `SelectType` enum updated accordingly
    - [Form controls preserve help IDs](#form-controls-preserve-help-ids) when warning is present
    - [Text controls](#text-controls-containing-div) add a containing `div` element
    - [All form controls have](#normalized-form-control-property-methods) `containerProps()`, `labelProps()`, and `inputProps()`; `FieldSet::labelProps()` targets the `legend` element
    - `id()` is available on `TextInput`, `EmailInput`, `Password`, `TextArea`, and `Select`; allowing you to override the default `id` based on `name`

- Updated or fixed:
    - [`PropertyInterface`](https://github.com/8fold/php-html-builder/blob/main/src/PropertyInterface.php) extends PHP's `Stringable`; add `public function __toString(): string` alongside `prop()` and `props()`.
    - `Select` [`radio()` and `checkbox()` IDs](#radio-and-checkbox-ids) use `<control-id>-option-<zero-based-index>` instead of `<name>-<option-value>`
    - `warningMessage` accessibility improved for each form control; `Fieldset` renders one warning for the group and [does not inspect child elements](#fieldset-does-not-inspect-children)
- Deprecated:
    - [`wrapperProps()` on form controls](#wrapper-on-form-controls)
- Deleted: None

## New

### `TextArea`

Escaping content remains caller-owned.

`TextArea` is a new form control, and its value is rendered as text content. It renders the supplied value without escaping, decoding, or double encoding.

```php
use Eightfold\HtmlBuilder\Forms\TextArea;

TextArea::create(
    $label,
    $name,
    $value = '', // caller-sanitized content, not a value attribute in <textarea>
    $warningMessage = ''
);

// Example of preparing a value; not a component capability.
// To display literal entity syntax such as `&amp;`, escape that text too
// (for example, supply `&amp;amp;`).
$value = "\nFirst line & second line\n";
$escapedValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

TextArea::create('Notes', 'notes', $escapedValue, 'Add more detail.')
    ->inputProps('rows 4', 'required required')
    ->labelProps('class notes-label');
```

Values are element text, not a `value` attribute. Spaces, tabs, and leading, internal, and trailing LF newlines are preserved: rendering adds one extra leading newline that HTML consumes.

Native HTML parsing normalizes CR and CRLF line endings to LF; browsers apply their native textarea submission rules.

- `inputProps()` targets the textarea and supports native `rows`, `required`, `readonly`, and `disabled` attributes.
- Generated `id` and `name` override conflicting input properties; `id()` also updates the label and warning associations.

### Select

`Select` defaults to a single dropdown.

```php
<select name="sample">
    <option value="1">1</option>
    <option value="2">2</option>
</select>
```

Choose different select controls by calling the method representing the desired type:

- `radio()` for single select, with exposed list;
- `checkbox()` for multiple select, with exposed list, which will append `[]` to the name if the supplied name doesn’t include it; and
- `multiple()` for multiple select box, requires keyboard interaction and including `[]` with the name (recommend checkbox instead).

`checked` and `multiple` passed to `inputProps()` are ignored; use the selection argument and mode methods instead.

```php
Select::create(
    'Label text',
    'name',
    ['a' => 'A', 'b' => 'B', 'c' => 'C'],
    ['a', 'c'] // selected, checked
)->checkbox();
```

See [`Select` attributes and selection](#select-attributes-and-selection) for native attribute targets and examples.

### Form controls preserve help IDs

`TextInput`, `EmailInput`, `Password`, `TextArea`, and all `Select` modes preserve help IDs supplied through `inputProps('aria-describedby help-id')` when adding a warning.

With a warning, the control sets `aria-invalid="true"` and appends `<control-id>-warning` to `aria-describedby`, removing duplicate references while preserving their order.

Use `content()` to supply the referenced help elements. Overriding the control's `id()` also changes its generated warning ID.

`tests/ControlGuidanceTest.php` checks these relationships across control types. Assertion failures identify the relationship, control type and ID, and rendered HTML, alongside PHPUnit's named case and expected/actual diff.

### Text controls containing `div`

An outer ‘<div>’ encloses the text-entry controls.

In 4.4.0, `TextInput`, `EmailInput`, and `Password` rendered their `label` and `input` as siblings; no container.

Update CSS child/sibling selectors, flex or grid layouts, and JavaScript traversal that assumed the old structure.

```html
<!-- 4.4.0 -->
<label for="email">Email</label>
<input id="email" name="email" type="text">

<!-- 5.0 -->
<div class="field">
  <label for="email">Email</label>
  <input id="email" name="email" type="text">
</div>
```

### Normalized form control property methods

Form control inner elements have property methods:

- `containerProps()` targets the outer container,
- `labelProps()` targets the label (or a Select group's legend), and
- `inputProps()` targets the input, textarea, or select.

For example:
```php
use Eightfold\HtmlBuilder\Forms\TextInput;

TextInput::create('Email', 'email')
    ->containerProps('class field')
    ->labelProps('class field-label')
    ->inputProps('class form-control');
```

Avoid using `inputProps()` or `labelProps()` to replace generated properties such as:

- `type`,
- `id`,
- `name`,
- `value`, or
- the label's `for` association.

For example, 4.4.0 allowed `TextInput::create('Email', 'email')->inputProps('type email')` to change its generated type; in 5.0, use `EmailInput` instead. Use constructor
arguments, `id()`, or the component/mode that matches the control you need.

Setting a custom ID changes the DOM ID and its generated label and warning associations, but does not change the submitted field name:

```php
TextInput::create('Email', 'account_email')->id('email-field');
// The input has id="email-field" and name="account_email".
```

Output (human-readable):

```html
<div class="field">
  <label class="field-label" for="email">Email</label>
  <input id="email" class="form-control" name="email" type="text">
</div>
```

## Updated or fixed

### Radio and checkbox IDs

In 4.4, `radio` and `checkbox` inputs would repeat the same name as their `id` attribute, which is invalid HTML; only one `id` with a given value per `html` element.

Now, option `a` in a group named `categories` changes from `categories-a` to `categories-option-0`.

Generated labels follow the new IDs automatically; update external labels, CSS selectors, and JavaScript that reference old IDs.

Calling `id()` changes the group's member-ID prefix, not its submitted `name`.

### `Fieldset` does not inspect child elements

```php
Fieldset::create($label, $name, $warningMessage = 'Warning message')
```

Will render one group warning with the ID `<name>-warning`.

String warnings are wrapped in a paragraph and retain caller-supplied markup.

`PropertyInterface` warnings retain their element, content, and other properties; the generated ID is applied to a local clone, leaving the caller's warning object unchanged. Repeated rendering is stable, and the same warning object can be reused across components with independent IDs.

**`Fieldset` does not inspect or rewrite arbitrary children passed to `elements()`**:Callers remain responsible for setting `aria-invalid` and `aria-describedby` on
member controls, including references to the group warning when appropriate.

## Deprecated

### `wrapperProps()` on form controls

`wrapperProps()` remains callable as a forwarding alias throughout 5.x. It
continues to set the same container properties, but is deprecated in favor of
`containerProps()` and emits `E_USER_DEPRECATED`. The native `#[Deprecated]`
attribute is supported by PHP 8.4 and later; removal is deferred until at
least 6.0. See [Migrating from wrapperProps](#migrating-from-wrapperprops).

In 5.0, use `containerProps()` instead of `wrapperProps()` on all form
components. This is a rename, not a change in property handling or target
elements. `wrapperProps()` remains a forwarding alias throughout 5.x and is
marked with PHP's native `#[Deprecated]` attribute (supported since PHP 8.4).
Calling it emits `E_USER_DEPRECATED`; removal is deferred until at least 6.0.
