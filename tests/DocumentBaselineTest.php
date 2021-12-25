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
    public function document_is_stringable(): void // phpcs:ignore
    {
        $expected = <<<html
            <!doctype html>
            <html lang="fr"><head><title>title</title><meta charset="ascii"><link rel="stylesheet" href="style.css"><script src="script.js"></script></head><body><p>paragraph content</p></body></html>
            html;

        $result = (string) Document::create('title', 'fr', 'ascii')
            ->head(
                Element::link()
                    ->omitEndTag()->props('rel stylesheet', 'href style.css'),
                Element::script()->props('src script.js')
            )->body(
                Element::p('paragraph content')
            );

        $this->assertSame($expected, $result);
    }
}
//
// test('Docment is stringable', function() {
//     expect(
        // (string) Document::create('title', 'fr', 'ascii')
        //     ->head(
        //         Element::link()
        //             ->omitEndTag()->props('rel stylesheet', 'href style.css'),
        //         Element::script()->props('src script.js')
        //     )->body(
        //         Element::p('paragraph content')
        //     )
    // )->toBe('<!doctype html>' . "\n" . '<html lang="fr"><head><title>title</title>' .
    //     '<meta charset="ascii"><link rel="stylesheet" href="style.css">' .
    //     '<script src="script.js"></script></head><body><p>paragraph content</p></body></html>'
//     );
// });
//
// test('Docment can have body', function() {
//     expect(
//         Document::create('title', 'fr', 'ascii')
//             ->head(
//                 Element::link()
//                     ->omitEndTag()->props('rel stylesheet', 'href style.css'),
//                 Element::script()->props('src script.js')
//             )->body(
//                 Element::p('paragraph content')
//             )->build()
//     )->toBe('<!doctype html>' . "\n" . '<html lang="fr"><head><title>title</title>' .
//         '<meta charset="ascii"><link rel="stylesheet" href="style.css">' .
//         '<script src="script.js"></script></head><body><p>paragraph content</p></body></html>'
//     );
// });
//
// test('Docment can have head', function() {
//     expect(
//         Document::create('title', 'fr', 'ascii')
//             ->head(
//                 Element::link()
//                     ->omitEndTag()->props('rel stylesheet', 'href style.css'),
//                 Element::script()->props('src script.js')
//             )->build()
//     )->toBe('<!doctype html>' . "\n" . '<html lang="fr"><head><title>title</title>' .
//         '<meta charset="ascii"><link rel="stylesheet" href="style.css">' .
//         '<script src="script.js"></script></head><body></body></html>'
//     );
// });
//
// test('Docment can change language and character set', function() {
//     expect(
//         Document::create('title', 'fr', 'ascii')->build()
//     )->toBe('<!doctype html>' . "\n" . '<html lang="fr"><head><title>title</title>' .
//         '<meta charset="ascii"></head><body></body></html>'
//     );
// });
//
// test('Docment has baseline', function() {
//     expect(
//         Document::create('title')->build()
//     )->toBe('<!doctype html>' . "\n" . '<html lang="en"><head><title>title</title>' .
//         '<meta charset="utf-8"></head><body></body></html>'
//     );
// });
//
// test('Document exists', function() {
//     $this->assertTrue(
//         class_exists(Document::class)
//     );
// });
