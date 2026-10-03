<?php

declare(strict_types=1);

namespace BristolianTest\Exception;

use Bristolian\Exception\ClipTimeTypeLogicException;
use BristolianTest\BaseTestCase;
use DataType\Exception\DataTypeLogicException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ClipTimeTypeLogicException::class)]
class ClipTimeTypeLogicExceptionTest extends BaseTestCase
{
    public function test_endValueMustBeInteger(): void
    {
        $exception = ClipTimeTypeLogicException::endValueMustBeInteger();
        $this->assertInstanceOf(DataTypeLogicException::class, $exception);
        $this->assertSame('end value must be integer', $exception->getMessage());
    }

    public function test_startValueMustBeInteger(): void
    {
        $exception = ClipTimeTypeLogicException::startValueMustBeInteger();
        $this->assertInstanceOf(DataTypeLogicException::class, $exception);
        $this->assertSame('start value must be integer', $exception->getMessage());
    }
}
