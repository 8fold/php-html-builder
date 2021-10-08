# 8fold HTML Builder for PHP

HTML Builder extends and is a web-specific implementation of
[8fold XML Builder](https://github.com/8fold/php-xml-builder); similar to how
HTML extends and is a specific implementation of XML.

## Installation

{how does one install the product}

## Usage (basic)

By default, HTML Builder can quickly output an HTML string with consistent
ordering of attributes for elements. The default order can be found in the
`Element` class, which can be overridden, if you would prefer a different
ordering. This helps create and enforce a consistent HTML pattern.

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

Further examples can be found in the `tests` folder with an example extension.

A note on attributes: If a property is not in the ordered list, and the value of
the property is the same as the name of the property, HTML Builder will presume
it's a boolean value and will truncate the definition in the HTML output.

```php
Element::input()->omitEndTag()->props("required required");
```

Output:

```html
<input required>
```

## Details

See [8fold XML Builder](https://github.com/8fold/php-xml-builder#readme) for
details.

## Usage (advanced)

TODO

HTML Builder has the ability to help create valid HTML documents based on the
HTML specification.

## Other

{links or descriptions or license, versioning, and governance}
