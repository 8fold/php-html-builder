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
        $context = $mode . " control.\nRendered HTML:\n" . $html;
        parent::assertNotFalse($warnings, 'Could not query object warnings for ' . $context);
        parent::assertCount(
            1,
            $warnings,
            'Expected exactly one warning div preserving class="custom-warning" for ' . $context
        );
        $links = $xpath->query(
            './/*[local-name()="a" and @href="/guidance/"]',
            $warnings->item(0)
        );
        parent::assertNotFalse($links, 'Could not query warning links for ' . $context);
        parent::assertCount(
            1,
            $links,
            'Expected the warning to preserve its link to /guidance/ for ' . $context
        );
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
        $context = $mode . " control.\nRendered HTML:\n" . $html;
        $xpath = self::xpath($html);
        $controls = $xpath->query(
            '//*[local-name()="input" or local-name()="select" or local-name()="textarea"]'
        );
        parent::assertNotFalse($controls, 'Could not query controls for ' . $context);
        parent::assertCount(
            in_array($mode, ['radio', 'checkbox'], true) ? 2 : 1,
            $controls,
            'Expected one control, or two option inputs for radio/checkbox groups, for ' . $context
        );

        $controlIds = [];
        foreach ($controls as $index => $control) {
            parent::assertInstanceOf(DomElement::class, $control, 'Expected a DOM element for ' . $context);
            $controlContext = sprintf(
                'Option/control %d (id="%s") in %s',
                $index + 1,
                $control->getAttribute('id'),
                $context
            );
            parent::assertSame(
                'preserved',
                $control->getAttribute('data-example'),
                'inputProps() must preserve unrelated attributes. ' . $controlContext
            );
            if ($expectedIds === []) {
                parent::assertFalse(
                    $control->hasAttribute('aria-describedby'),
                    'Without help references or a warning, aria-describedby must be absent. ' . $controlContext
                );
            } else {
                parent::assertSame(
                    $expectedIds,
                    preg_split('/\s+/', trim($control->getAttribute('aria-describedby'))),
                    'aria-describedby must preserve help IDs and append the warning ID once, in order. '
                        . $controlContext
                );
            }
            if ($hasWarning) {
                parent::assertSame(
                    'true',
                    $control->getAttribute('aria-invalid'),
                    'A warning must set aria-invalid="true", overriding any supplied valid state. ' . $controlContext
                );
            } else {
                parent::assertFalse(
                    $control->hasAttribute('aria-invalid'),
                    'Without a warning or explicit invalid state, aria-invalid must be absent. ' . $controlContext
                );
            }

            $controlId = $control->getAttribute('id');
            parent::assertNotSame('', $controlId, 'Each control needs a nonempty ID. ' . $controlContext);
            $controlIds[] = $controlId;
            $labels = $xpath->query(
                '//*[local-name()="label" and @for="' . $controlId . '"]'
            );
            parent::assertNotFalse($labels, 'Could not query associated labels. ' . $controlContext);
            parent::assertCount(
                1,
                $labels,
                'Expected exactly one label with for="' . $controlId . '". ' . $controlContext
            );
        }
        parent::assertCount(
            count($controlIds),
            array_unique($controlIds),
            'Controls must have unique IDs so labels target the correct input. ' . $context
        );

        foreach ($expectedIds as $reference) {
            $targets = $xpath->query('//*[@id="' . $reference . '"]');
            parent::assertNotFalse($targets, 'Could not query guidance target "' . $reference . '" for ' . $context);
            parent::assertCount(
                1,
                $targets,
                'aria-describedby reference "' . $reference . '" must resolve to exactly one element for ' . $context
            );
        }
        $warnings = $xpath->query('//*[@id="' . $id . '-warning"]');
        parent::assertNotFalse($warnings, 'Could not query warning target "' . $id . '-warning" for ' . $context);
        parent::assertCount(
            $hasWarning ? 1 : 0,
            $warnings,
            'Expected ' . ($hasWarning ? 'exactly one' : 'no') . ' warning with id="' . $id . '-warning" for ' . $context
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
