<?php

declare(strict_types = 1);

namespace Bristolian\MarkdownRenderer;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\UrlAutolinkParser;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Normalizer\SlugNormalizer;

class CommonMarkRenderer implements MarkdownRenderer
{
    public function renderFile(string $filepath): string
    {
        $markdown = @file_get_contents($filepath);

        if ($markdown === false) {
            throw MarkdownRendererException::fileNotFound($filepath);
        }

        return $this->render($markdown);
    }

    public function render(string $markdown): string
    {
        $config = [
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'id_prefix' => 'user-content',
                'insert' => 'after',
                'title' => 'Permalink',
                'symbol' => "\u{00A0}\u{00A0}🔗",
            ],
            'slug_normalizer' => [
                // ... other options here ...
                'instance' => new SlugNormalizer(),
            ],
            'html_input' => 'allow',
            'disallowed_raw_html' => [
                'disallowed_tags' => [
                    'title', 'textarea', 'style', 'xmp',
                    //'iframe',
                    'noembed', 'noframes', 'script', 'plaintext'
                ],
            ],
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        if (true) {
            // This works
            // $environment->addExtension(new AutolinkExtension());
            $environment->addInlineParser(new UrlAutolinkParser());
            $environment->addExtension(new DisallowedRawHtmlExtension());
            $environment->addExtension(new StrikethroughExtension());
            $environment->addExtension(new TableExtension());
            $environment->addExtension(new TaskListExtension());
        }
        else {
            // This errors "Unexpected item 'disallowed_raw_html'."
            $environment->addExtension(new GithubFlavoredMarkdownExtension());
        }

        // Add the extension
        $environment->addExtension(new FootnoteExtension());

        $converter = new MarkdownConverter($environment);

        return $converter->convert($markdown)->getContent();
    }
}
