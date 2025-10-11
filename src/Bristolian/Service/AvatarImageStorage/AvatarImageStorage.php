<?php

namespace Bristolian\Service\AvatarImageStorage;

use Bristolian\UploadedFiles\UploadedFile;

interface AvatarImageStorage
{
    /**
     * @param string $user_id
     * @param UploadedFile $uploadedFile
     * @param string[] $allowedExtensions
     * @return string|UploadError The avatar_image_id or an error
     * @throws \Bristolian\Exception\BristolianException
     */
    public function storeAvatarForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions
    ): string|UploadError;
}

