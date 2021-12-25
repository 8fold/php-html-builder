<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Tests;

use PHPUnit\Framework\TestCase;

use Eightfold\HTMLBuilder\Tests\Extensions\ElementExtension;

class ElementExtensionBaselineTest extends TestCase
{
    /**
     * @test
     */
    public function has_ordered_properties(): void // phpcs:ignore
    {
        $exptected = <<<html
            <a class="some-style" href="https://8fold.pro" id="unique" data-testing="test" required>link</a>
            html;

        $result = ElementExtension::a('link')
            ->props(
                'required required',
                'href https://8fold.pro',
                'class some-style',
                'id unique',
                'data-testing test'
            )->build();

        $this->assertSame($exptected, $result);
    }
}
