<?php

declare(strict_types=1);

namespace BristolianTest\MarkdownRenderer;

use BristolianTest\BaseTestCase;
use Bristolian\MarkdownRenderer\FakeMarkdownRenderer;
use Bristolian\MarkdownRenderer\MarkdownRendererException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\MarkdownRenderer\FakeMarkdownRenderer::class)]

class FakeMarkdownRendererForPagesTest extends BaseTestCase
{
    public function testWorks(): void
    {
        $renderer = new FakeMarkdownRenderer();
        $result = $renderer->renderFile(__FILE__);
        $this->assertStringContainsString(basename(__FILE__), $result);

        $some_text = "Some text";
        $result = $renderer->render($some_text);
        $this->assertSame($some_text, $result);
    }
}
