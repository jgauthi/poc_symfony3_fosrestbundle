<?php

namespace App\Twig;

use App\Service\MarkdownTransformer;
use Twig_Extension;

class MarkdownExtension extends Twig_Extension
{
    private $markdownTransformer;

    public function __construct(MarkdownTransformer $markdownTransformer)
    {
        $this->markdownTransformer = $markdownTransformer;
    }

    public function getName(): string
    {
        return 'app_markdown';
    }

    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter('mardownify', [$this, 'parseMarkdown'], ['is_safe' => ['html']]),
        ];
    }

    public function parseMarkdown(string $str): string
    {
        return $this->markdownTransformer->parse($str);
    }
}
