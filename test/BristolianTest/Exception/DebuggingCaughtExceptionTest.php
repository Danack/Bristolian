<?php

declare(strict_types=1);

namespace BristolianTest\Exception;

use Bristolian\Exception\DebuggingCaughtException;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Exception\DebuggingCaughtException
 */
class DebuggingCaughtExceptionTest extends BaseTestCase
{
    public function test_extends_exception_and_carries_message(): void
    {
        $message = 'Test caught';
        $exception = new DebuggingCaughtException($message);
        $this->assertInstanceOf(DebuggingCaughtException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
