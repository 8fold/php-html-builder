<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\Forms\Fieldset;
use Eightfold\HtmlBuilder\Forms\TextInput;

final class FieldsetTest extends TestCase
{
    #[Test]
    public function object_warning_is_rendered_locally_without_mutating_the_original(): void
    {
        $warning = Element::div(
            Element::a('Review guidance')->props('href /guidance/')
        )->props('id original', 'class custom-warning', 'data-example preserved');
        $before = (string) $warning;
        $fieldset = Fieldset::create('Group', 'group', $warning);
        $expected = '<fieldset><legend>Group</legend>'
            . '<div id="group-warning" class="custom-warning" data-example="preserved">'
            . '<a href="/guidance/">Review guidance</a></div></fieldset>';

        for ($render = 0; $render < 3; $render++) {
            $html = (string) $fieldset;
            parent::assertSame($before, (string) $warning);
            parent::assertSame($expected, $html);
            parent::assertSame(1, substr_count($html, 'id="group-warning"'));
        }
    }

    #[Test]
    public function shared_object_warning_keeps_independent_component_ids(): void
    {
        $warning = Element::p('Check the value.')->props('id original', 'class warning');
        $before = (string) $warning;
        $first = Fieldset::create('First', 'first', $warning);
        $second = Fieldset::create('Second', 'second', $warning);
        $input = TextInput::create('Text', 'text', warningMessage: $warning)->id('custom');

        $firstHtml = (string) $first;
        $secondHtml = (string) $second;
        $inputHtml = (string) $input;

        parent::assertSame(
            '<fieldset><legend>First</legend><p id="first-warning" class="warning">'
                . 'Check the value.</p></fieldset>',
            $firstHtml
        );
        parent::assertSame(
            '<fieldset><legend>Second</legend><p id="second-warning" class="warning">'
                . 'Check the value.</p></fieldset>',
            $secondHtml
        );
        parent::assertStringContainsString(
            '<p id="custom-warning" class="warning">Check the value.</p>',
            $inputHtml
        );
        parent::assertSame($firstHtml, (string) $first);
        parent::assertSame($secondHtml, (string) $second);
        parent::assertSame($inputHtml, (string) $input);
        parent::assertSame($before, (string) $warning);
    }

    #[Test]
    public function string_warning_preserves_markup_in_one_paragraph(): void
    {
        $fieldset = Fieldset::create('Group', 'group', '<strong>Check</strong> the value.');
        $expected = '<fieldset><legend>Group</legend>'
            . '<p id="group-warning"><strong>Check</strong> the value.</p></fieldset>';

        parent::assertSame($expected, (string) $fieldset);
        parent::assertSame($expected, (string) $fieldset);
    }

    #[Test]
    public function empty_warning_emits_no_warning_element(): void
    {
        parent::assertSame(
            '<fieldset><legend>Group</legend></fieldset>',
            (string) Fieldset::create('Group', 'group')
        );
    }

    #[Test]
    public function warning_does_not_rewrite_arbitrary_child_controls(): void
    {
        $child = Element::input()->omitEndTag()->props(
            'id child',
            'aria-describedby child-help group-warning',
            'aria-invalid false'
        );
        $before = (string) $child;
        $rawChild = '<input id="raw" aria-describedby="raw-help">';
        $fieldset = Fieldset::create('Group', 'group', 'Check the group.')->elements(
            Element::div($child),
            $rawChild
        );
        $expected = '<fieldset><legend>Group</legend><div>' . $before . '</div>'
            . $rawChild . '<p id="group-warning">Check the group.</p></fieldset>';

        parent::assertSame($expected, (string) $fieldset);
        parent::assertSame($expected, (string) $fieldset);
        parent::assertSame($before, (string) $child);
    }
}
