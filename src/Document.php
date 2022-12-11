<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder;

use Stringable;

use Eightfold\XMLBuilder\Contracts\Buildable;
use Eightfold\XMLBuilder\Implementations\Buildable as BuildableImp;

use Eightfold\HTMLBuilder\Element;

class Document implements Buildable
{
    use BuildableImp;

    /**
     * @var array<string|Stringable>
     */
    private array $head = [];

    /**
     * @var array<string|Stringable>
     */
    private array $body = [];

    public static function create(
        string $title,
        string $lang = 'en',
        string $charset = 'utf-8'
    ): Document {
        return new static($title, $lang, $charset);
    }

    final private function __construct(
        private string $title,
        private string $lang,
        private string $charset
    ) {
    }

    public function head(string|Stringable ...$content): Document
    {
        $this->head = $content;
        return $this;
    }

    public function body(string|Stringable ...$content): Document
    {
        $this->body = $content;
        return $this;
    }

    public function __toString(): string
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
     * @return array<string|Stringable>
     */
    private function headContent(): array
    {
        return $this->head;
    }

    /**
     * @return array<string|Stringable>
     */
    private function bodyContent(): array
    {
        return $this->body;
    }
}
