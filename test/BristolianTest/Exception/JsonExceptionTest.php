<?php

declare(strict_types=1);

namespace BristolianTest\Exception;

use Bristolian\Exception\JsonException;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Exception\JsonException
 */
class JsonExceptionTest extends BaseTestCase
{
    public function test_extends_exception_and_carries_message(): void
    {
        $message = 'Invalid JSON';
        $exception = new JsonException($message);
        $this->assertInstanceOf(JsonException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
