<?php

use Eightfold\HTMLBuilder\Document;

use Eightfold\XMLBuilder\Comment;

use Eightfold\HTMLBuilder\Element;

test('Document is speedy', function() {
    $start = hrtime(true);

    $head = [];
    for ($i = 0; $i < 10; $i++) {
        if ($i > 2 and $i < 8) {
            $head[] = Comment::create($i);

        }

        $head[] = Element::meta()->props("id {$i}");
    }

    $body = [];
    for ($i = 0; $i < 10; $i++) {
        if ($i > 1 and $i < 9) {
            $body[] = Comment::create($i);

        }

        $body[] = Element::p("Hello, {$i}!");
    }

    $result = (string) Document::create('title of my site')
        ->head(...$head)
        ->body(...$body);

    $end = memory_get_usage();

    expect($result)->toBe(<<<doc
        <!doctype html>
        <html lang="en"><head><title>title of my site</title><meta charset="utf-8"><meta id="0"></meta><meta id="1"></meta><meta id="2"></meta>
        <!-- 3 -->
        <meta id="3"></meta>
        <!-- 4 -->
        <meta id="4"></meta>
        <!-- 5 -->
        <meta id="5"></meta>
        <!-- 6 -->
        <meta id="6"></meta>
        <!-- 7 -->
        <meta id="7"></meta><meta id="8"></meta><meta id="9"></meta></head><body><p>Hello, 0!</p><p>Hello, 1!</p>
        <!-- 2 -->
        <p>Hello, 2!</p>
        <!-- 3 -->
        <p>Hello, 3!</p>
        <!-- 4 -->
        <p>Hello, 4!</p>
        <!-- 5 -->
        <p>Hello, 5!</p>
        <!-- 6 -->
        <p>Hello, 6!</p>
        <!-- 7 -->
        <p>Hello, 7!</p>
        <!-- 8 -->
        <p>Hello, 8!</p><p>Hello, 9!</p></body></html>
        doc
    );

    $end = hrtime(true);

    $elapsed = $end - $start;
    $ms      = $elapsed/1e+6;

    expect($ms)->toBeLessThan(1);
});

test('Document is small', function() {
    $start = memory_get_usage();;

    $head = [];
    for ($i = 0; $i < 10; $i++) {
        if ($i > 2 and $i < 8) {
            $head[] = Comment::create($i);

        }

        $head[] = Element::meta()->props("id {$i}");
    }

    $body = [];
    for ($i = 0; $i < 10; $i++) {
        if ($i > 1 and $i < 9) {
            $body[] = Comment::create($i);

        }

        $body[] = Element::p("Hello, {$i}!");
    }

    $result = (string) Document::create('title of my site')
        ->head(...$head)
        ->body(...$body);

    $end = memory_get_usage();

    expect($result)->toBe(<<<doc
        <!doctype html>
        <html lang="en"><head><title>title of my site</title><meta charset="utf-8"><meta id="0"></meta><meta id="1"></meta><meta id="2"></meta>
        <!-- 3 -->
        <meta id="3"></meta>
        <!-- 4 -->
        <meta id="4"></meta>
        <!-- 5 -->
        <meta id="5"></meta>
        <!-- 6 -->
        <meta id="6"></meta>
        <!-- 7 -->
        <meta id="7"></meta><meta id="8"></meta><meta id="9"></meta></head><body><p>Hello, 0!</p><p>Hello, 1!</p>
        <!-- 2 -->
        <p>Hello, 2!</p>
        <!-- 3 -->
        <p>Hello, 3!</p>
        <!-- 4 -->
        <p>Hello, 4!</p>
        <!-- 5 -->
        <p>Hello, 5!</p>
        <!-- 6 -->
        <p>Hello, 6!</p>
        <!-- 7 -->
        <p>Hello, 7!</p>
        <!-- 8 -->
        <p>Hello, 8!</p><p>Hello, 9!</p></body></html>
        doc
    );

    $used = $end - $start;
    $kb   = round($used/1024.2);

    expect($kb)->toBeLessThan(15);
});
