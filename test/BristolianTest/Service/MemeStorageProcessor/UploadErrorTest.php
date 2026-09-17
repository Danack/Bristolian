<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeStorageProcessor;

use Bristolian\Service\MemeStorageProcessor\UploadError;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\MemeStorageProcessor\UploadError::class, '__construct')]
#[CoversMethod(\Bristolian\Service\MemeStorageProcessor\UploadError::class, 'duplicateOriginalFilename')]
#[CoversMethod(\Bristolian\Service\MemeStorageProcessor\UploadError::class, 'unsupportedFileType')]
#[CoversMethod(\Bristolian\Service\MemeStorageProcessor\UploadError::class, 'uploadedFileUnreadable')]

class UploadErrorTest extends BaseTestCase
{
    public function test_uploadedFileUnreadable_returns_error_with_constant_message(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $this->assertSame(UploadError::UNREADABLE_FILE_MESSAGE, $error->error_message);
        $this->assertNull($error->error_code);
        $this->assertNull($error->error_data);
    }

    public function test_unsupportedFileType_returns_error_with_constant_message(): void
    {
        $error = UploadError::unsupportedFileType();
        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $error->error_message);
        $this->assertNull($error->error_code);
        $this->assertNull($error->error_data);
    }

    public function test_duplicateOriginalFilename_returns_error_with_code_and_data(): void
    {
        $error = UploadError::duplicateOriginalFilename('myfile.jpg');
        $this->assertStringContainsString('myfile.jpg', $error->error_message);
        $this->assertSame(UploadError::DUPLICATE_FILENAME, $error->error_code);
        $this->assertSame(['filename' => 'myfile.jpg'], $error->error_data);
    }
}
