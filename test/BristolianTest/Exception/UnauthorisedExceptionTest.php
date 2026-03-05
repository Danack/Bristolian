<?php

declare(strict_types=1);

namespace BristolianTest\Exception;

use Bristolian\Exception\BristolianException;
use Bristolian\Exception\UnauthorisedException;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Exception\UnauthorisedException
 */
class UnauthorisedExceptionTest extends BaseTestCase
{
    public function test_extends_bristolian_exception_and_carries_message(): void
    {
        $message = 'You must be logged in';
        $exception = new UnauthorisedException($message);
        $this->assertInstanceOf(UnauthorisedException::class, $exception);
        $this->assertInstanceOf(BristolianException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
