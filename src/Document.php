<?php
declare(strict_types=1);

namespace Eightfold\HTMLBuilder;

use Stringable;

use Eightfold\HTMLBuilder\Element;

class Document implements Stringable
{
    /**
     * @var array<string|Stringable>
     */
    private array $head = [];

    /**
     * @var array<string|Stringable>
     */
    private array $body = [];

    /**
     * @var array<string>
     */
    private array $bodyProps = [];

    public static function create(
        string|Stringable $title,
        string $lang = 'en',
        string $charset = 'utf-8'
    ): Document {
        return new static($title, $lang, $charset);
    }

    final private function __construct(
        private string|Stringable $title,
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

    public function bodyProps(string ...$props): Document
    {
        $this->bodyProps = $props;
        return $this;
    }

    public function __toString(): string
    {
        $pageTitle = $this->title();
        if (
            is_string($pageTitle) and
            str_starts_with($pageTitle, '<title>') === false
        ) {
            $pageTitle = Element::title($this->title());
        }

        $doctype = '<!doctype html>' . "\n";
        $html = (string) Element::html(
            Element::head(
                $pageTitle,
                Element::meta()->omitEndTag()->props($this->charset()),
                ...$this->headContent()
            ),
            Element::body(...$this->bodyContent())->props(...$this->bodyProps)
        )->props($this->lang());
        return $doctype . $html;
    }

    private function title(): string|Stringable
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
