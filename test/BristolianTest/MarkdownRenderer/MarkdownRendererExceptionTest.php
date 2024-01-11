<?php

declare(strict_types = 1);

namespace BristolianTest\MarkdownRenderer;

use BristolianTest\BaseTestCase;
use Bristolian\MarkdownRenderer\MarkdownRendererException;

/**
 * @coversNothing
 */
class MarkdownRendererExceptionTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\MarkdownRenderer\MarkdownRendererException
     */
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
