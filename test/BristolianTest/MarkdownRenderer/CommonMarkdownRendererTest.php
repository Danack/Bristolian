<?php

declare(strict_types = 1);

namespace BristolianTest\MarkdownRenderer;

use BristolianTest\BaseTestCase;
use Bristolian\MarkdownRenderer\CommonMarkRenderer;
use Bristolian\MarkdownRenderer\MarkdownRendererException;

/**
 * @coversNothing
 */
class CommonMarkdownRendererTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\MarkdownRenderer\CommonMarkRenderer
     */
    public function testWorks(): void
    {
        $commonMarkRenderer = new CommonMarkRenderer();
        $result = $commonMarkRenderer->render("Hello [a link](http://www.example.com)");
        $this->assertSame(
            "<p>Hello <a href=\"http://www.example.com\">a link</a></p>\n",
            $result
        );
    }

    /**
     * @covers \Bristolian\MarkdownRenderer\CommonMarkRenderer
     */
    public function testFileWorks(): void
    {
        $commonMarkRenderer = new CommonMarkRenderer();
        $result = $commonMarkRenderer->renderFile(__DIR__ . "/test.md");
        $this->assertSame(
            "<p>Hello <a href=\"http://www.example.com\">a link</a></p>\n",
            $result
        );
    }

    /**
     * @covers \Bristolian\MarkdownRenderer\CommonMarkRenderer
     */
    public function testFileException(): void
    {
        $commonMarkRenderer = new CommonMarkRenderer();
        $this->expectException(MarkdownRendererException::class);
        $commonMarkRenderer->renderFile(__DIR__ . "/does_not_exist.md");
    }
}
