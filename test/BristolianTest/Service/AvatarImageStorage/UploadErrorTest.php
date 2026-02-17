<?php

declare(strict_types=1);

namespace BristolianTest\Service\AvatarImageStorage;

use Bristolian\Service\AvatarImageStorage\UploadError;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UploadErrorTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\AvatarImageStorage\UploadError::uploadedFileUnreadable
     */
    public function test_uploadedFileUnreadable_returns_error_with_message(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $this->assertSame('Uploaded file is not readable', $error->error_message);
    }

    /**
     * @covers \Bristolian\Service\AvatarImageStorage\UploadError::extensionNotAllowed
     */
    public function test_extensionNotAllowed_returns_error_with_extension_in_message(): void
    {
        $error = UploadError::extensionNotAllowed('exe');
        $this->assertSame("File extension 'exe' is not allowed", $error->error_message);
    }

    /**
     * @covers \Bristolian\Service\AvatarImageStorage\UploadError::imageTooSmall
     */
    public function test_imageTooSmall_returns_error_with_dimensions_in_message(): void
    {
        $error = UploadError::imageTooSmall(100, 200, 512);
        $this->assertSame('Image is too small (100x200). Must be at least 512x512 pixels', $error->error_message);
    }
}
