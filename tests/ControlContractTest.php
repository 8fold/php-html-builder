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

    private static function xpath(string $html): XPath
    {
        return new XPath(HTMLDocument::createFromString(
            '<!doctype html><html><head><title>Test</title></head><body>'
                . $html . '</body></html>'
        ));
    }
}
