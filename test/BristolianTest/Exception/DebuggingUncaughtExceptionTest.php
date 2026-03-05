<?php

declare(strict_types=1);

namespace BristolianTest\Exception;

use Bristolian\Exception\DebuggingUncaughtException;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Exception\DebuggingUncaughtException
 */
class DebuggingUncaughtExceptionTest extends BaseTestCase
{
    public function test_extends_exception_and_carries_message(): void
    {
        $message = 'Test uncaught';
        $exception = new DebuggingUncaughtException($message);
        $this->assertInstanceOf(DebuggingUncaughtException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
