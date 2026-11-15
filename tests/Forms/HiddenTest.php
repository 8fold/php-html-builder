<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Forms\Hidden;

use Eightfold\HtmlBuilder\Element;

class HiddenTest extends TestCase
{
    #[Test]
    public function can_set_value(): void // phpcs:ignore
    {
        $expected = <<<html
        <input name="test" type="hidden" value="something important">
        html;

        $result = (string) Hidden::create('test','something important');

        parent::assertSame($expected, $result);
    }
}
