<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\EmptyElement;


class ElementBaselineTest extends TestCase
{
    #[Test]
    public function can_have_empty_props(): void // phpcs:ignore
    {
        $expected = <<<html
        <a>link</a>
        html;

        $result = (string) Element::a('link')->props('');

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function has_ordered_properties(): void // phpcs:ignore
    {
        // phpcs:disable
        $expected = <<<html
        <a id="unique" class="some-style" href="https://8fold.pro" data-testing="test" required>link</a>
        html;
        // phpcs:enable

        $result = (string) Element::a('link')->props(
            'required required',
            'href https://8fold.pro',
            'class some-style',
            'id unique',
            'data-testing test'
        );

        parent::assertSame($expected, $result);
    }

    #[Test]
    public function has_correct_selfclosing_string(): void // phpcs:ignore
    {
        $expected = <<<html
            <img>
            html;

        $result = (string) Element::img()->omitEndTag();

        parent::assertSame($expected, $result);

        $result = (string) EmptyElement::img();

        parent::assertSame($expected, $result);
    }
}
