<?php

use Eightfold\HTMLBuilder\Tests\Extensions\ElementExtension;

test('Element has ordered properties', function() {
    expect(
        ElementExtension::a('link')
            ->props(
                'required required',
                'href https://8fold.pro',
                'class some-style',
                'id unique',
                'data-testing test'
            )->build()
    )->toBe(
        '<a class="some-style" href="https://8fold.pro" id="unique" data-testing="test" required>link</a>'
    );
});
