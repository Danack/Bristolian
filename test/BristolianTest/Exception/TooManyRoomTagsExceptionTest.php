<?php

declare(strict_types=1);

namespace BristolianTest\Exception;

use Bristolian\Exception\BristolianException;
use Bristolian\Exception\TooManyRoomTagsException;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Exception\TooManyRoomTagsException::forMaxReached
 */
class TooManyRoomTagsExceptionTest extends BaseTestCase
{
    public function test_forMaxReached_returns_exception_with_max_in_message(): void
    {
        $max = 10;
        $exception = TooManyRoomTagsException::forMaxReached($max);
        $this->assertInstanceOf(TooManyRoomTagsException::class, $exception);
        $this->assertInstanceOf(BristolianException::class, $exception);
        $this->assertStringContainsString('Maximum tags per room', $exception->getMessage());
        $this->assertStringContainsString((string) $max, $exception->getMessage());
    }
}
