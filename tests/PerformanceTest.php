<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Tests;

use PHPUnit\Framework\TestCase;

use Eightfold\HTMLBuilder\Tests\Extensions\ElementExtension;

use Eightfold\XMLBuilder\Comment;

use Eightfold\HTMLBuilder\Document;
use Eightfold\HTMLBuilder\Element;

class PerformanceTest extends TestCase
{
    private function head(): array
    {
        $head = [];
        for ($i = 0; $i < 10; $i++) {
            if ($i > 2 and $i < 8) {
                $head[] = Comment::create(strval($i));
            }
            $head[] = Element::meta()->props("id {$i}");
        }
        return $head;
    }

    private function body(): array
    {
        $body = [];
        for ($i = 0; $i < 10; $i++) {
            if ($i > 1 and $i < 9) {
                $body[] = Comment::create(strval($i));
            }
            $body[] = Element::p("Hello, {$i}!");
        }
        return $body;
    }

    /**
     * @test
     */
    public function document_is_speedy(): void // phpcs:ignore
    {
        $start = hrtime(true);

        $result = (string) Document::create('title of my site')
            ->head(...$this->head())
            ->body(...$this->body()) . "\n";

        $end = hrtime(true);

        $expected = file_get_contents(__DIR__ . '/performance.html');

        $this->assertSame($expected, $result);

        $elapsed = $end - $start;
        $ms      = $elapsed/1e+6;

        $this->assertLessThan(0.43, $ms);
    }

    /**
     * @test
     */
    public function document_is_small(): void // phpcs:ignore
    {
        $start = memory_get_usage();

        $result = (string) Document::create('title of my site')
            ->head(...$this->head())
            ->body(...$this->body()) . "\n";

        $end = memory_get_peak_usage();

        $expected = file_get_contents(__DIR__ . '/performance.html');

        $this->assertSame($expected, $result);

        $used = $end - $start;
        $kb   = round($used/1024.2);

        $this->assertLessThan(65, $kb);
    }
}
