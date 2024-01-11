<?php

declare(strict_types = 1);

namespace BristolianTest\ExternalMarkdownRenderer;

use Bristolian\MarkdownRenderer\CommonMarkRenderer;
use BristolianTest\BaseTestCase;
use UrlFetcher\FakeUrlFetcher;
use Bristolian\ExternalMarkdownRenderer\StandardExternalMarkdownRenderer;

/**
 * @coversNothing
 */
class ExternalMarkdownRendererTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\ExternalMarkdownRenderer\StandardExternalMarkdownRenderer
     */
    public function testWorks(): void
    {
        $exampleMarkdown = "Hello [a link](http://www.example.com)";
        $urlFetcher = new FakeUrlFetcher($exampleMarkdown);
        $commonMarkRenderer = new CommonMarkRenderer();
        $renderer = new StandardExternalMarkdownRenderer(
            $urlFetcher,
            $commonMarkRenderer
        );

        // Url isn't used
        $result = $renderer->renderUrl("http://example.com/foo");
        $this->assertSame(
            "<p>Hello <a href=\"http://www.example.com\">a link</a></p>\n",
            $result
        );
    }
}
