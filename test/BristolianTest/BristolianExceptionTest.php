<?php

namespace BristolianTest;

namespace BristolianTest;

use Bristolian\BristolianException;

/**
 * @coversNothing
 */
class BristolianExceptionTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\BristolianException
     */
    public function testWorks_cannot_instantiate()
    {
        $exception = BristolianException::cannot_instantiate();
        $this->assertInstanceOf(BristolianException::class, $exception);
    }

    /**
     * @covers \Bristolian\BristolianException
     */
    public function testWorks_env_variable_is_not_string()
    {
        $name = "foo";
        $value = 12345;

        $exception = BristolianException::env_variable_is_not_string($name, $value);

        $this->assertStringContainsString($name, $exception);
        $this->assertStringContainsString((string)$value, $exception);
    }
}