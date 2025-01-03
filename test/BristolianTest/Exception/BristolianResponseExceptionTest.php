<?php

namespace BristolianTest;

namespace BristolianTest\Exception;

use Bristolian\Exception\BristolianResponseException;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class BristolianResponseExceptionTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Exception\BristolianResponseException
     */
    public function testWorks_cannot_instantiate()
    {
        $exception = BristolianResponseException::failedToOpenFile(__FILE__);
        $this->assertInstanceOf(BristolianResponseException::class, $exception);
        $this->assertStringContainsString(__FILE__, $exception->getMessage());
    }
}
