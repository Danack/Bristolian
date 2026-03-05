<?php

declare(strict_types=1);

namespace BristolianTest\Exception;

use Bristolian\Exception\BristolianException;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class BristolianExceptionTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Exception\BristolianException::cannot_instantiate
     */
    public function test_cannot_instantiate_returns_exception_with_constant_message(): void
    {
        $exception = BristolianException::cannot_instantiate();
        $this->assertInstanceOf(BristolianException::class, $exception);
        $this->assertSame(BristolianException::CANNOT_INSTANTIATE, $exception->getMessage());
    }

    /**
     * @covers \Bristolian\Exception\BristolianException::env_variable_is_not_string
     */
    public function test_env_variable_is_not_string_includes_name_and_value_in_message(): void
    {
        $name = "foo";
        $value = 12345;

        $exception = BristolianException::env_variable_is_not_string($name, $value);

        $this->assertInstanceOf(BristolianException::class, $exception);
        $this->assertStringContainsString($name, $exception->getMessage());
        $this->assertStringContainsString((string) $value, $exception->getMessage());
    }
}
