<?php

namespace Bristolian\Service\AvatarImageStorage;

use Bristolian\UploadedFiles\UploadedFile;

/**
 * Fake implementation of AvatarImageStorage for testing.
 */
final class FakeAvatarImageStorage implements AvatarImageStorage
{
    public function __construct(
        private string|UploadError $storeResult
    ) {
    }

    /**
     * @param string[] $allowedExtensions
     */
    public function storeAvatarForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions
    ): string|UploadError {
        return $this->storeResult;
    }
}
