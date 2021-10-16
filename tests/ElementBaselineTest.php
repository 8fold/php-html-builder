<?php

use Eightfold\HTMLBuilder\Element;

test('Element can have empty props', function() {
    expect(
        (string) Element::a('link')->props('')
    )->toBe(<<<html
        <a>link</a>
        html
    );
});

test('Element has ordered properties', function() {
    expect(
        Element::a('link')
            ->props(
                'required required',
                'href https://8fold.pro',
                'class some-style',
                'id unique',
                'data-testing test'
            )->build()
    )->toBe(
        '<a id="unique" class="some-style" href="https://8fold.pro" data-testing="test" required>link</a>'
    );
});

test('Element has correct self-closing string', function() {
    expect(
        Element::tag()->omitEndTag()->build()
    )->toBe(
        '<tag>'
    );
});

test('Element exists', function() {
    $this->assertTrue(
        class_exists(Element::class)
    );
});
