<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use Dom\Element as DomElement;
use Dom\HTMLDocument;
use Dom\XPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Forms\TextArea;

final class TextAreaTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function values(): iterable
    {
        yield 'empty' => [''];
        yield 'plain text' => ['Notes for the reader'];
        yield 'internal newline' => ["First line\nSecond line"];
        yield 'leading newline' => ["\nFirst line"];
        yield 'trailing newline' => ["Last line\n"];
        yield 'multiple surrounding newlines' => ["\n\nFirst\nSecond\n\n"];
        yield 'whitespace only' => [" \t\n "];
        yield 'ampersand and quotes' => ['A & B say "hello" and \'goodbye\''];
        yield 'literal entity syntax' => ['&amp; &lt; &#10; &#x3c;'];
        yield 'closing tag and script text' => ['</textarea><script>window.example = true;</script><textarea>'];
        yield 'mixed case closing tag' => ['</TeXtArEa><p>Outside</p>'];
        yield 'UTF-8 text' => ["Caf\u{00e9} \u{1f642}"];
    }

    #[Test]
    #[DataProvider('values')]
    public function caller_escaped_values_round_trip_without_double_encoding(string $value): void
    {
        $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $control = TextArea::create('Notes', 'notes', $escaped);
        $html = (string) $control;
        $xpath = self::xpath($html);
        $textareas = $xpath->query('//*[local-name()="textarea"]');
        parent::assertNotFalse($textareas);
        parent::assertCount(1, $textareas, "Value must not create extra textareas.\nRendered HTML:\n" . $html);
        $textarea = $textareas->item(0);
        parent::assertInstanceOf(DomElement::class, $textarea);
        parent::assertSame(
            $value,
            $textarea->textContent,
            "Caller-escaped text must round-trip without trimming or double encoding.\nRendered HTML:\n" . $html
        );
        parent::assertFalse($textarea->hasAttribute('value'), 'Textarea values belong in text content.');
        parent::assertSame(
            0,
            $xpath->query('//*[local-name()="script" or local-name()="p"]')->length,
            'Caller-escaped markup-like text must not become document elements.'
        );
        parent::assertStringContainsString(
            '>' . "\n" . $escaped . '</textarea>',
            $html,
            'The supplied value must be rendered verbatim after the parser-consumed newline.'
        );
        parent::assertSame($html, (string) $control, 'Repeated rendering must not transform the supplied value.');
    }

    /** @return iterable<string, array{int}> */
    public static function escapingOptions(): iterable
    {
        yield 'quotes' => [ENT_QUOTES | ENT_SUBSTITUTE];
        yield 'double quotes only' => [ENT_COMPAT | ENT_SUBSTITUTE];
        yield 'no quotes' => [ENT_NOQUOTES | ENT_SUBSTITUTE];
        yield 'HTML5 entities' => [ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5];
    }

    #[Test]
    #[DataProvider('escapingOptions')]
    public function caller_selected_escaping_options_are_preserved(int $flags): void
    {
        $value = '<notes> & "quoted" and \'single\'';
        $escaped = htmlspecialchars($value, $flags, 'UTF-8');
        $html = (string) TextArea::create('Notes', 'notes', $escaped);
        parent::assertStringContainsString(
            '>' . "\n" . $escaped . '</textarea>',
            $html,
            'TextArea must not impose its own encoding options on caller-escaped values.'
        );
        parent::assertSame(
            $value,
            self::xpath($html)->query('//*[local-name()="textarea"]')->item(0)->textContent
        );
    }

    #[Test]
    public function supplied_content_is_not_automatically_sanitized(): void
    {
        $value = '<b>Literal source</b> &amp; "quotes"';
        parent::assertStringContainsString(
            '>' . "\n" . $value . '</textarea>',
            (string) TextArea::create('Notes', 'notes', $value),
            'Sanitization belongs to the caller, consistently with the other controls.'
        );
    }

    #[Test]
    public function carriage_returns_follow_native_html_newline_normalization(): void
    {
        $xpath = self::xpath((string) TextArea::create('Notes', 'notes', "\r\nFirst\rSecond\r\n"));
        parent::assertSame(
            "\nFirst\nSecond\n",
            $xpath->query('//*[local-name()="textarea"]')->item(0)->textContent,
            'HTML parsing normalizes CR and CRLF to LF without losing leading or trailing line breaks.'
        );
    }

    #[Test]
    public function native_properties_and_custom_identity_target_the_textarea(): void
    {
        $html = (string) TextArea::create('Notes', 'notes', 'A &amp; B')
            ->id('custom-notes')
            ->inputProps(
                'id ignored',
                'name ignored',
                'rows 4',
                'required required',
                'readonly readonly',
                'disabled disabled'
            )->wrapperProps('class notes-wrapper')->labelProps('class notes-label');
        $xpath = self::xpath($html);
        $textarea = $xpath->query('//*[local-name()="textarea"]')->item(0);
        parent::assertInstanceOf(DomElement::class, $textarea);
        parent::assertSame('custom-notes', $textarea->getAttribute('id'), 'The explicit ID must win.');
        parent::assertSame('notes', $textarea->getAttribute('name'), 'The declared submitted name must win.');
        parent::assertSame('4', $textarea->getAttribute('rows'));
        foreach (['required', 'readonly', 'disabled'] as $attribute) {
            parent::assertTrue($textarea->hasAttribute($attribute), $attribute . ' must reach the textarea.');
        }
        parent::assertFalse($textarea->hasAttribute('aria-invalid'), 'No warning must mean no generated invalid state.');
        parent::assertFalse($textarea->hasAttribute('aria-describedby'), 'No guidance must mean no generated references.');
        parent::assertSame(
            1,
            $xpath->query('//*[local-name()="label" and @for="custom-notes" and @class="notes-label"]')->length,
            'The styled label must reference the custom control ID.'
        );
        parent::assertSame(
            1,
            $xpath->query('//*[local-name()="div" and @class="notes-wrapper"]')->length
        );
        parent::assertSame(
            0,
            $xpath->query('//*[local-name()="div" and (@rows or @required or @readonly or @disabled)]')->length,
            'Native control attributes must not leak onto the wrapper.'
        );
        parent::assertStringContainsString('A &amp; B', $html, 'Caller-escaped ampersands must remain unchanged.');
    }

    private static function xpath(string $html): XPath
    {
        return new XPath(HTMLDocument::createFromString(
            '<!doctype html><html><head><title>Textarea test</title></head><body>'
                . $html . '</body></html>'
        ));
    }
}
