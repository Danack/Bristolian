<?php

declare(strict_types=1);

namespace BristolianTest\Exception;

use Bristolian\Exception\BristolianResponseException;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class BristolianResponseExceptionTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Exception\BristolianResponseException::failedToOpenFile
     */
    public function test_failedToOpenFile_returns_exception_with_filename_in_message(): void
    {
        $filename = '/some/path/to/file.txt';
        $exception = BristolianResponseException::failedToOpenFile($filename);
        $this->assertInstanceOf(BristolianResponseException::class, $exception);
        $this->assertStringContainsString($filename, $exception->getMessage());
        $this->assertStringContainsString('Failed to open file', $exception->getMessage());
    }
}
