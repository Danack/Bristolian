<?php

declare(strict_types = 1);

namespace BristolianTest\MarkdownRenderer;

use BristolianTest\BaseTestCase;
use Bristolian\MarkdownRenderer\MarkdownRendererException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\MarkdownRenderer\MarkdownRendererException::class)]

class MarkdownRendererExceptionTest extends BaseTestCase
{
    public function testBasic(): void
    {
        $name = 'John';

        $exception = MarkdownRendererException::fileNotFound($name);

        $this->assertStringContainsString(
            $name,
            $exception->getMessage()
        );
    }
}
