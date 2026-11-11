# 8fold HTML Builder for PHP

HTML Builder is a web-specific string compiler.

Warning: Users of this library are responsible for sanitizing content.

- [Installation](#installation)
- [Usage](#usage)
  - [`Element⁣`](#element)
  - [`EmptyElement`](#emptyelement)
  - [`Document`](#document)
  - [`Comment`](#comment)
  - [Properties](#properties)⁣
- [Extending](#extending)⁣
- [Performance](#performance)⁣
- Migrating (`docs/`)
  - [5.0 to 5.1](./docs/migrating-from-050000-050100.md)
    - [`EmptyElement`](./docs/migrating-from-050000-050100.md#emptyelement)
    - [Static analysis](./docs/migrating-from-050000-050100.md#static-analysis)⁣
  - [4.x to 5.0](#migrating-from-4x-to-50)⁣

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
```

Output:
```html
<p id="intro">Hello, World!</p>
```

Non-empty element using magic method:
```php
Element::p('Hello, World!')->props('id intro');
```

Output:
```html
<p id="intro">Hello, World!</p>
```

Empty element using magic method:
```php
Element::img()->omitEndTag()->props('src /path/to/image.png');
```

Output:
```html
<img src="/path/to/image.php">
```

### `EmptyElement`

The [`EmptyElement`](https://github.com/8fold/php-html-builder/blob/main/src/EmptyElement.php) class was introduced in the 5.1 release and is designed to mirror the input shorthand of HTML those elements compared to using the `Element` class to do the same.

```php
use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\EmptyElement;

// empty element use Element
Element::img()->omitEndTag()->props('src /path/to/image.png');
```

Output:
```html
<img src="/path/to/image.php">
```

Using `EmptyElement`:
```php
EmptyElement::img('src /path/to/image.png');

// output same as previous
```

### `Document`

The [`Document`](https://github.com/8fold/php-html-builder/blob/main/src/Document.php) class gives you to generate a complete HTML document entirely in PHP.

```php
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
```

Output:

```html
<!-- compressed -->
<!doctype html>
<html lang="en"><head><title>title</title><meta charset="utf-8"><link rel="stylesheet" href="style.css"><script src="script.js"></script></head><body><p>Hello, World!</p></body></html>

<!-- human-readable -->
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

There’s even a [`Comment`](https://github.com/8fold/php-html-builder/blob/main/src/Comment.php) component for adding inline HTML comments.

```php
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
```

Output (human-readable):
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
```

Output:
```html
<input required>
```

Properties me be added individually to facilitate dynamic construction.

```php
use Eightfold\HtmlBuild\Element;

$callout = Element::div('Information in the callout.')
    ->props('class callout', 'aria-role alert');
    
if ($isWarning) {
    $callout = $callout->prop('is warning');
}
```

Output
```html
<!-- human-readable: `$isWarning = false` -->
<div class="callout" aria-role="alert">
    Information in the callout.
</div>

<!-- human-readable: $isWarning = true -->
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

## Details

See [8fold XML Builder](https://github.com/8fold/php-xml-builder#readme) for PHP HTML Builder’s sibling.

## Other

- [Code of Conduct](https://github.com/8fold/php-html-builder/blob/master/.github/CODE_OF_CONDUCT.md)
- [Contributing](https://github.com/8fold/php-html-builder/blob/master/.github/CONTRIBUTING.md)
- [Governance](https://github.com/8fold/php-html-builder/blob/master/.github/GOVERNANCE.md)
- [Versioning](https://github.com/8fold/php-html-builder/blob/master/.github/VERSIONING.md)
- [Security](https://github.com/8fold/php-html-builder/blob/master/.github/SECURITY.md)
