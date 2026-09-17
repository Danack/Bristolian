<?php

declare(strict_types=1);

namespace BristolianTest\Service\AvatarImageStorage;

use Bristolian\Service\AvatarImageStorage\UploadError;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\AvatarImageStorage\UploadError::class, '__construct')]
#[CoversMethod(\Bristolian\Service\AvatarImageStorage\UploadError::class, 'extensionNotAllowed')]
#[CoversMethod(\Bristolian\Service\AvatarImageStorage\UploadError::class, 'imageTooSmall')]
#[CoversMethod(\Bristolian\Service\AvatarImageStorage\UploadError::class, 'uploadedFileUnreadable')]

class UploadErrorTest extends BaseTestCase
{
    public function test_uploadedFileUnreadable_returns_error_with_message(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $this->assertSame('Uploaded file is not readable', $error->error_message);
    }

    public function test_extensionNotAllowed_returns_error_with_extension_in_message(): void
    {
        $error = UploadError::extensionNotAllowed('exe');
        $this->assertSame("File extension 'exe' is not allowed", $error->error_message);
    }

    public function test_imageTooSmall_returns_error_with_dimensions_in_message(): void
    {
        $error = UploadError::imageTooSmall(100, 200, 512);
        $this->assertSame('Image is too small (100x200). Must be at least 512x512 pixels', $error->error_message);
    }
}
