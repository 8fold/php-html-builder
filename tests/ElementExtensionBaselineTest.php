<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HTMLBuilder\Tests\Extensions\ElementExtension;

class ElementExtensionBaselineTest extends TestCase
{
    #[Test]
    public function has_ordered_properties(): void // phpcs:ignore
    {
        $exptected = <<<html
            <a is="link" id="unique" class="some-style" href="https://8fold.pro" data-testing="test" required>link</a>
            html;

        $result = (string) ElementExtension::a('link')->props(
                'required required',
                'href https://8fold.pro',
                'class some-style',
                'id unique',
                'data-testing test',
                'is link'
            );

        $this->assertSame($exptected, $result);
    }
}
