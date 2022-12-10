<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Tests;

use PHPUnit\Framework\TestCase;

use Eightfold\HTMLBuilder\Element;

class ElementBaselineTest extends TestCase
{
    /**
     * @test
     */
    public function can_have_empty_props(): void // phpcs:ignore
    {
        $expected = <<<html
            <a>link</a>
            html;

        $result = (string) Element::a('link')->props('');

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function has_ordered_properties(): void // phpcs:ignore
    {
        $expected = <<<html
            <a id="unique" class="some-style" href="https://8fold.pro" data-testing="test" required>link</a>
            html;

        $result = Element::a('link')
            ->props(
                'required required',
                'href https://8fold.pro',
                'class some-style',
                'id unique',
                'data-testing test'
            )->build();

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function has_correct_selfclosing_string(): void // phpcs:ignore
    {
        $expected = <<<html
            <tag>
            html;

        $result = Element::tag()->omitEndTag()->build();

        $this->assertSame($expected, $result);
    }
}
