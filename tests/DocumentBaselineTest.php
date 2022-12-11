<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder\Tests;

use PHPUnit\Framework\TestCase;

use Eightfold\HTMLBuilder\Document;

use Eightfold\HTMLBuilder\Element;

class DocumentBaselineTest extends TestCase
{
    /**
     * @test
     */
    public function is_stringable(): void // phpcs:ignore
    {
        $expected = <<<html
            <!doctype html>
            <html lang="fr"><head><title>title</title><meta charset="ascii"><link rel="stylesheet" href="style.css"><script src="script.js"></script></head><body><p>paragraph content</p></body></html>
            html;

        $result = (string) Document::create('title', 'fr', 'ascii')->head(
                Element::link()
                    ->omitEndTag()->props('rel stylesheet', 'href style.css'),
                Element::script()->props('src script.js')
            )->body(
                Element::p('paragraph content')
            );

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function can_have_body(): void // phpcs:ignore
    {
        $expected = <<<html
            <!doctype html>
            <html lang="fr"><head><title>title</title><meta charset="ascii"><link rel="stylesheet" href="style.css"><script src="script.js"></script></head><body><p>paragraph content</p></body></html>
            html;

        $result = (string) Document::create('title', 'fr', 'ascii')->head(
                Element::link()
                    ->omitEndTag()->props('rel stylesheet', 'href style.css'),
                Element::script()->props('src script.js')
            )->body(
                Element::p('paragraph content')
            );

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function can_have_head(): void // phpcs:ignore
    {
        $expected = <<<html
            <!doctype html>
            <html lang="fr"><head><title>title</title><meta charset="ascii"><link rel="stylesheet" href="style.css"><script src="script.js"></script></head><body></body></html>
            html;

        $result = (string) Document::create('title', 'fr', 'ascii')->head(
                Element::link()
                    ->omitEndTag()->props('rel stylesheet', 'href style.css'),
                Element::script()->props('src script.js')
            );

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function can_change_lang_and_char_set(): void // phpcs:ignore
    {
        $expected = <<<html
            <!doctype html>
            <html lang="fr"><head><title>title</title><meta charset="ascii"></head><body></body></html>
            html;

        $result = (string) Document::create('title', 'fr', 'ascii');

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function has_baseline(): void // phpcs:ignore
    {
        $expected = <<<html
            <!doctype html>
            <html lang="en"><head><title>title</title><meta charset="utf-8"></head><body></body></html>
            html;

        $result = (string) Document::create('title');

        $this->assertSame($expected, $result);
    }
}
