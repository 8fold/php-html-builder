<?php

declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Dom\Element as DomElement;
use Dom\HTMLDocument;
use Dom\XPath;
use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\Forms\EmailInput;
use Eightfold\HtmlBuilder\Forms\Password;
use Eightfold\HtmlBuilder\Forms\Select;
use Eightfold\HtmlBuilder\Forms\TextArea;
use Eightfold\HtmlBuilder\Forms\TextInput;

final class ControlContractTest extends TestCase
{
    /** @return iterable<string, array{string, string, string, list<string>}> */
    public static function valueCases(): iterable
    {
        foreach (['text', 'email'] as $mode) {
            foreach (['empty' => '', 'nonempty' => 'current@example.com', 'zero' => '0'] as $case => $value) {
                foreach (['no warning' => '', 'warning' => 'Check the value.'] as $guidance => $warning) {
                    foreach ([
                        'single' => ['value stale@example.com'],
                        'repeated' => ['value first@example.com', 'value second@example.com', 'value '],
                        'mixed case' => ['VALUE stale@example.com', 'Value another@example.com'],
                        'bare' => ['value', 'value stale@example.com']
                    ] as $declaration => $properties) {
                        yield "$mode: $case, $guidance, $declaration" => [$mode, $value, $warning, $properties];
                    }
                }
            }
        }
    }

    /** @param list<string> $properties */
    #[Test]
    #[DataProvider('valueCases')]
    public function constructor_owns_value_including_empty(
        string $mode,
        string $value,
        string $warning,
        array $properties
    ): void {
        $control = match ($mode) {
            'text' => TextInput::create('Field', 'field', $value, $warning),
            'email' => EmailInput::create('Field', 'field', $value, $warning)
        };
        $html = (string) $control->inputProps(...[
            ...$properties,
            'data-value preserved',
            'placeholder Enter a value',
            'required required'
        ]);
        $inputs = self::xpath($html)->query('//*[local-name()="input"]');
        parent::assertNotFalse($inputs);
        parent::assertCount(1, $inputs, $html);
        $input = $inputs->item(0);
        parent::assertInstanceOf(DomElement::class, $input);
        parent::assertSame($value === '' ? null : $value, $input->getAttribute('value'), $html);
        parent::assertSame($value !== '', $input->hasAttribute('value'), $html);
        parent::assertSame('preserved', $input->getAttribute('data-value'), $html);
        parent::assertSame('Enter a value', $input->getAttribute('placeholder'), $html);
        parent::assertTrue($input->hasAttribute('required'), $html);
    }

    #[Test]
    public function element_still_skips_empty_properties_without_clearing_previous_values(): void
    {
        parent::assertSame(
            '<input value="existing">',
            (string) Element::input()->omitEndTag()->props('value existing', 'value ', 'value')
        );
    }

    /** @return iterable<string, array{string, ?string}> */
    public static function labelCases(): iterable
    {
        foreach (['text', 'email', 'password', 'textarea', 'dropdown', 'multiple'] as $mode) {
            foreach (['default' => null, 'overridden' => 'custom-field'] as $case => $id) {
                yield "$mode: $case ID" => [$mode, $id];
            }
        }
    }

    #[Test]
    #[DataProvider('labelCases')]
    public function generated_label_association_overrides_caller_properties(string $mode, ?string $id): void
    {
        $control = match ($mode) {
            'text' => TextInput::create('Field', 'field'),
            'email' => EmailInput::create('Field', 'field'),
            'password' => Password::create('Field', 'field'),
            'textarea' => TextArea::create('Field', 'field'),
            'dropdown' => Select::create('Field', 'field', ['a' => 'A']),
            'multiple' => Select::create('Field', 'field', ['a' => 'A'])->multiple()
        };
        if ($id !== null) {
            $control->id($id);
        }
        $html = (string) $control->labelProps(
            'for other',
            'class field-label',
            'for another',
            'data-for preserved'
        );
        $xpath = self::xpath($html);
        $controls = $xpath->query(
            '//*[local-name()="input" or local-name()="select" or local-name()="textarea"]'
        );
        parent::assertNotFalse($controls);
        parent::assertCount(1, $controls, $html);
        $input = $controls->item(0);
        parent::assertInstanceOf(DomElement::class, $input);
        $controlId = $input->getAttribute('id');
        parent::assertSame($id ?? 'field', $controlId, $html);
        $labels = $xpath->query('//*[local-name()="label"]');
        parent::assertNotFalse($labels);
        parent::assertCount(1, $labels, $html);
        $label = $labels->item(0);
        parent::assertInstanceOf(DomElement::class, $label);
        parent::assertSame($controlId, $label->getAttribute('for'), $html);
        parent::assertSame('field-label', $label->getAttribute('class'), $html);
        parent::assertSame('preserved', $label->getAttribute('data-for'), $html);
        $targets = $xpath->query('//*[@id="' . $label->getAttribute('for') . '"]');
        parent::assertNotFalse($targets);
        parent::assertCount(1, $targets, $html);
        parent::assertSame($input, $targets->item(0), $html);
    }

    private static function xpath(string $html): XPath
    {
        return new XPath(HTMLDocument::createFromString(
            '<!doctype html><html><head><title>Test</title></head><body>'
                . $html . '</body></html>'
        ));
    }
}
