<?php

declare(strict_types=1);

namespace Eightfold\HTMLBuilder;

use Eightfold\XMLBuilder\Comment;
use Eightfold\XMLBuilder\Contracts\Buildable;
use Eightfold\HTMLBuilder\Element;

class Document implements Buildable
{
    private string $title   = '';
    private string $lang    = 'en';
    private string $charset = 'utf-8';

    /**
     * @var array<Element|Comment>
     */
    private array $head = [];

    /**
     * @var array<Element|Comment|string>
     */
    private array $body = [];

    public static function create(
        string $title,
        string $lang = 'en',
        string $charset = 'utf-8'
    ): Document {
        return new static($title, $lang, $charset);
    }

    final public function __construct(
        string $title,
        string $lang = 'en',
        string $charset = 'utf-8'
    ) {
        $this->title   = $title;
        $this->lang    = $lang;
        $this->charset = $charset;
    }

    // TODO: PHP8 - Element|Comment
    /**
     * @param  Element|Comment $content
     */
    public function head(Element|Comment ...$content): Document
    {
        $this->head = $content;
        return $this;
    }

    // TODO: PHP8 - Element|Comment
    /**
     * @param Element|Comment|string $content
     */
    public function body(Element|Comment|string ...$content): Document
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

    /**
     * @return array<Element|Comment> $content
     */
    private function headContent(): array
    {
        return $this->head;
    }

    /**
     * @return array<Element|Comment|string> $content
     */
    private function bodyContent(): array
    {
        return $this->body;
    }
}
