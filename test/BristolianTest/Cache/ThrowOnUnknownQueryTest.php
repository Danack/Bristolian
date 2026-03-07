<?php

declare(strict_types=1);

namespace BristolianTest\Cache;

use Bristolian\Cache\ThrowOnUnknownQuery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bristolian\Cache\ThrowOnUnknownQuery
 */
class ThrowOnUnknownQueryTest extends TestCase
{
    public function testThrowsRuntimeException(): void
    {
        $handler = new ThrowOnUnknownQuery();

        $this->expectException(\RuntimeException::class);
        $handler->handle('SELECT * FROM users');
    }

    public function testExceptionMessageContainsQuery(): void
    {
        $handler = new ThrowOnUnknownQuery();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('SELECT * FROM users');
        $handler->handle('SELECT * FROM users');
    }

    public function testExceptionMessageTruncatesLongQuery(): void
    {
        $handler = new ThrowOnUnknownQuery();
        $longQuery = str_repeat('A', 500);

        try {
            $handler->handle($longQuery);
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $exception) {
            $message = $exception->getMessage();
            $this->assertStringContainsString(str_repeat('A', 200), $message);
            $this->assertStringNotContainsString(str_repeat('A', 201), $message);
        }
    }

    public function testExceptionMessageContainsFixInstructions(): void
    {
        $handler = new ThrowOnUnknownQuery();

        try {
            $handler->handle('SELECT 1');
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $exception) {
            $message = $exception->getMessage();
            $this->assertStringContainsString('QueryTagMapping.php', $message);
            $this->assertStringContainsString('getExactMappings()', $message);
            $this->assertStringContainsString('getPatternMappings()', $message);
            $this->assertStringContainsString('Unknown query not in cache tag mapping', $message);
        }
    }
}
