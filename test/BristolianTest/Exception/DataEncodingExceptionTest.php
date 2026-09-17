<?php

namespace BristolianTest\Exception;

use Bristolian\Exception\DataEncodingException;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Exception\DataEncodingException::class)]

class DataEncodingExceptionTest extends BaseTestCase
{
    public function testWorks()
    {
        $message = "Failed to encode data";
        $error = "Invalid UTF-8 sequence";

        $exception = new DataEncodingException($message, $error);

        $this->assertInstanceOf(DataEncodingException::class, $exception);
        $this->assertStringContainsString($message, $exception->getMessage());
        $this->assertStringContainsString($error, $exception->getMessage());
        $this->assertStringContainsString(" : ", $exception->getMessage());
    }
}
