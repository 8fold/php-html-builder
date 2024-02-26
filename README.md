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

## Performance

The `/comparisons` folder can be used to run performance tests locally to compare the same output using:

1. straight HTML,
2. standard PHP,
3. PHP HTML Builder alongside standard PHP (the Element class),
4. PHP HTML Builder only (the Document class), and
5. PHP HTML Builder only using PSR-4 autoloading.

These results are from February 26th, 2024:

|Configuration |Average time in milliseconds |Median time in milliseconds |
|:---|---:|---:|
|1 |0.0399683 |0.0017415 |
|2 |0.0953241 |0.0160480 |
|3 |1.6026525 |1.4603320 |
|4 |1.7849769 |1.7918970 |
|5 |8.9957415 |8.8755520 |

Things worth noting (as rough interpretations):

1. PHP being used as a template engine has a negligible impact on speed.
2. Using `require_once` without PSR-4 autoloading decreases speed by roughly 1.5 milliseconds; however, once the code is made available, very little additional cost seems to be present.
3. Using PSR-4 decreases speed by roughly 6 milliseconds; however, ensures everything is present and available.

## Details

See [8fold XML Builder](https://github.com/8fold/php-xml-builder#readme) for more
details.

## Other

- [Code of Conduct](https://github.com/8fold/php-html-builder/blob/master/.github/CODE_OF_CONDUCT.md)
- [Contributing](https://github.com/8fold/php-html-builder/blob/master/.github/CONTRIBUTING.md)
- [Governance](https://github.com/8fold/php-html-builder/blob/master/.github/GOVERNANCE.md)
- [Versioning](https://github.com/8fold/php-html-builder/blob/master/.github/VERSIONING.md)
- [Security](https://github.com/8fold/php-html-builder/blob/master/.github/SECURITY.md)
