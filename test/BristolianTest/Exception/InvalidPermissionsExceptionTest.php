<?php

declare(strict_types=1);

namespace BristolianTest\Exception;

use Bristolian\Exception\InvalidPermissionsException;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Exception\InvalidPermissionsException::class)]
class InvalidPermissionsExceptionTest extends BaseTestCase
{
    public function test_extends_exception_and_carries_message(): void
    {
        $message = 'Insufficient permissions';
        $exception = new InvalidPermissionsException($message);
        $this->assertInstanceOf(InvalidPermissionsException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
