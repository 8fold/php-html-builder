<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\EmptyElement;

class EmptyElementBaselineTest extends TestCase
{
    #[Test]
    public function has_correct_selfclosing_string(): void // phpcs:ignore
    {
        $expected = <<<html
            <img>
            html;

        $result = (string) EmptyElement::h1();

        $this->assertSame($expected, $result);
    }
}
