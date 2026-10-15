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
use Eightfold\HtmlBuilder\Forms\Select;
use Eightfold\HtmlBuilder\Forms\TextArea;
use Eightfold\HtmlBuilder\Forms\TextInput;
use Eightfold\HtmlBuilder\PropertyInterface;

final class ControlGuidanceTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function controls(): iterable
    {
        foreach (['text', 'email', 'textarea', 'dropdown', 'multiple', 'radio', 'checkbox'] as $mode) {
            yield $mode => [$mode];
        }
    }

    /**
     * @return iterable<string, array{string, list<string>, string, list<string>}>
     */
    public static function guidanceCases(): iterable
    {
        $cases = [
            'neither' => [[], '', []],
            'help only' => [['aria-describedby field-help'], '', ['field-help']],
            'warning only' => [[], 'Check the value.', ['field-warning']],
            'help and warning' => [
                ['aria-describedby field-help'],
                'Check the value.',
                ['field-help', 'field-warning']
            ],
            'multiple help IDs' => [
                ['aria-describedby field-help field-format'],
                'Check the value.',
                ['field-help', 'field-format', 'field-warning']
            ],
            'irregular whitespace' => [
                ["aria-describedby \tfield-help  \nfield-format\t"],
                'Check the value.',
                ['field-help', 'field-format', 'field-warning']
            ],
            'duplicate references' => [
                ['aria-describedby field-help field-warning field-help'],
                'Check the value.',
                ['field-help', 'field-warning']
            ],
            'repeated properties' => [
                ['aria-describedby field-help', 'aria-describedby field-format field-help'],
                'Check the value.',
                ['field-help', 'field-format', 'field-warning']
            ],
            'warning overrides valid state' => [
                ['aria-invalid false', 'aria-describedby field-help'],
                'Check the value.',
                ['field-help', 'field-warning']
            ]
        ];

        foreach (self::controls() as [$mode]) {
            foreach ($cases as $case => [$properties, $warning, $ids]) {
                yield $mode . ': ' . $case => [$mode, $properties, $warning, $ids];
            }
        }
    }

    /**
     * @param list<string> $properties
     * @param list<string> $expectedIds
     */
    #[Test]
    #[DataProvider('guidanceCases')]
    public function every_control_preserves_guidance_relationships(
        string $mode,
        array $properties,
        string $warning,
        array $expectedIds
    ): void {
        $html = self::render($mode, $warning, $properties);
        self::assertGuidance($html, $mode, $expectedIds, $warning !== '');
    }

    #[Test]
    #[DataProvider('controls')]
    public function object_warnings_preserve_markup_and_are_rendered_once(string $mode): void
    {
        $warning = Element::div(
            Element::a('Review guidance')->props('href /guidance/')
        )->props('class custom-warning');

        $html = self::render($mode, $warning, ['aria-describedby field-help']);
        self::assertGuidance($html, $mode, ['field-help', 'field-warning'], true);
        $xpath = self::xpath($html);
        $warnings = $xpath->query(
            '//*[local-name()="div" and @id="field-warning" and @class="custom-warning"]'
        );
        parent::assertNotFalse($warnings);
        parent::assertCount(1, $warnings);
        $links = $xpath->query(
            './/*[local-name()="a" and @href="/guidance/"]',
            $warnings->item(0)
        );
        parent::assertNotFalse($links);
        parent::assertCount(1, $links);
    }

    #[Test]
    #[DataProvider('controls')]
    public function overridden_identity_drives_warning_associations(string $mode): void
    {
        $html = self::render($mode, 'Check the value.', ['aria-describedby field-help'], 'custom');
        self::assertGuidance($html, $mode, ['field-help', 'custom-warning'], true, 'custom');
    }

    /**
     * @param list<string> $properties
     */
    private static function render(
        string $mode,
        string|PropertyInterface $warning,
        array $properties,
        string $id = 'field'
    ): string {
        $control = match ($mode) {
            'text' => TextInput::create('Field', 'field', '', $warning),
            'email' => EmailInput::create('Field', 'field', '', $warning),
            'textarea' => TextArea::create('Field', 'field', '', $warning),
            'dropdown' => Select::create('Field', 'field', ['a' => 'A', 'b' => 'B'], [], $warning),
            'multiple' => Select::create(
                'Field', 'field', ['a' => 'A', 'b' => 'B'], [], $warning
            )->multiple(),
            'radio' => Select::create(
                'Field', 'field', ['a' => 'A', 'b' => 'B'], [], $warning
            )->radio(),
            'checkbox' => Select::create(
                'Field', 'field', ['a' => 'A', 'b' => 'B'], [], $warning
            )->checkbox()
        };

        return (string) $control->id($id)
            ->inputProps(...[...$properties, 'data-example preserved'])
            ->content(
                Element::p('Help')->props('id field-help'),
                Element::p('Format')->props('id field-format')
            );
    }

    /** @param list<string> $expectedIds */
    private static function assertGuidance(
        string $html,
        string $mode,
        array $expectedIds,
        bool $hasWarning,
        string $id = 'field'
    ): void {
        $xpath = self::xpath($html);
        $controls = $xpath->query(
            '//*[local-name()="input" or local-name()="select" or local-name()="textarea"]'
        );
        parent::assertNotFalse($controls);
        parent::assertCount(in_array($mode, ['radio', 'checkbox'], true) ? 2 : 1, $controls);

        $controlIds = [];
        foreach ($controls as $control) {
            parent::assertInstanceOf(DomElement::class, $control);
            parent::assertSame('preserved', $control->getAttribute('data-example'));
            if ($expectedIds === []) {
                parent::assertFalse($control->hasAttribute('aria-describedby'));
            } else {
                parent::assertSame(
                    $expectedIds,
                    preg_split('/\s+/', trim($control->getAttribute('aria-describedby')))
                );
            }
            if ($hasWarning) {
                parent::assertSame('true', $control->getAttribute('aria-invalid'));
            } else {
                parent::assertFalse($control->hasAttribute('aria-invalid'));
            }

            $controlId = $control->getAttribute('id');
            parent::assertNotSame('', $controlId);
            $controlIds[] = $controlId;
            $labels = $xpath->query(
                '//*[local-name()="label" and @for="' . $controlId . '"]'
            );
            parent::assertNotFalse($labels);
            parent::assertCount(1, $labels);
        }
        parent::assertCount(count($controlIds), array_unique($controlIds));

        foreach ($expectedIds as $reference) {
            $targets = $xpath->query('//*[@id="' . $reference . '"]');
            parent::assertNotFalse($targets);
            parent::assertCount(1, $targets);
        }
        $warnings = $xpath->query('//*[@id="' . $id . '-warning"]');
        parent::assertNotFalse($warnings);
        parent::assertCount($hasWarning ? 1 : 0, $warnings);
    }

    private static function xpath(string $html): XPath
    {
        return new XPath(HTMLDocument::createFromString(
            '<!doctype html><html><head><title>Test</title></head><body>'
                . $html . '</body></html>'
        ));
    }
}
