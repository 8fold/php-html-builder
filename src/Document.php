<?php

declare(strict_types=1);

namespace Eightfold\HTMLBuilder;

use Eightfold\HTMLBuilder\Element;

class Document
{
    private $title   = '';
    private $lang    = 'en';
    private $charset = 'utf-8';

    private $head = [];
    private $body = [];

    public static function create(
        string $title,
        string $lang = 'en',
        string $charset = 'utf-8'
    ): Document
    {
        return new static($title, $lang, $charset);
    }

    final public function __construct(
        string $title,
        string $lang = 'en',
        string $charset = 'utf-8'
    )
    {
        $this->title   = $title;
        $this->lang    = $lang;
        $this->charset = $charset;
    }

    // TODO: PHP8 - Element|Comment
    public function head(...$content)
    {
        $this->head = $content;
        return $this;
    }

    // TODO: PHP8 - Element|Comment
    public function body(...$content)
    {
        $this->body = $content;
        return $this;
    }

    public function build(): string
    {
        $doctype = '<!doctype html>' . "\n";
        return $doctype . Element::html(
            Element::head(
                Element::title($this->title()),
                Element::meta()->omitEndTag()->props($this->charset()),
                ...$this->headContent()
            ),
            Element::body(...$this->bodyContent())
        )->props($this->lang())->build();
    }

    public function __toString(): string
    {
        return $this->build();
    }

    private function title(): string
    {
        return $this->title;
    }

    private function lang(): string
    {
        return 'lang ' . $this->lang;
    }

    private function charset(): string
    {
        return 'charset ' . $this->charset;
    }

    private function headContent(): array
    {
        return $this->head;
    }

    private function bodyContent(): array
    {
        return $this->body;
    }
}
