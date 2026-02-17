<?php

declare(strict_types=1);

namespace BristolianTest\Service\BristolStairImageStorage;

use Bristolian\Service\BristolStairImageStorage\UploadError;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UploadErrorTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\BristolStairImageStorage\UploadError::uploadedFileUnreadable
     */
    public function test_uploadedFileUnreadable_returns_error_with_constant_message(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $this->assertSame(UploadError::UNREADABLE_FILE_MESSAGE, $error->error_message);
    }

    /**
     * @covers \Bristolian\Service\BristolStairImageStorage\UploadError::unsupportedFileType
     */
    public function test_unsupportedFileType_returns_error_with_constant_message(): void
    {
        $error = UploadError::unsupportedFileType();
        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $error->error_message);
    }
}
