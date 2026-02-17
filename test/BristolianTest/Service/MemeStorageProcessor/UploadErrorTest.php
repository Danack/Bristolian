<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeStorageProcessor;

use Bristolian\Service\MemeStorageProcessor\UploadError;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UploadErrorTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\UploadError::uploadedFileUnreadable
     */
    public function test_uploadedFileUnreadable_returns_error_with_constant_message(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $this->assertSame(UploadError::UNREADABLE_FILE_MESSAGE, $error->error_message);
        $this->assertNull($error->error_code);
        $this->assertNull($error->error_data);
    }

    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\UploadError::unsupportedFileType
     */
    public function test_unsupportedFileType_returns_error_with_constant_message(): void
    {
        $error = UploadError::unsupportedFileType();
        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $error->error_message);
        $this->assertNull($error->error_code);
        $this->assertNull($error->error_data);
    }

    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\UploadError::duplicateOriginalFilename
     */
    public function test_duplicateOriginalFilename_returns_error_with_code_and_data(): void
    {
        $error = UploadError::duplicateOriginalFilename('myfile.jpg');
        $this->assertStringContainsString('myfile.jpg', $error->error_message);
        $this->assertSame(UploadError::DUPLICATE_FILENAME, $error->error_code);
        $this->assertSame(['filename' => 'myfile.jpg'], $error->error_data);
    }
}
