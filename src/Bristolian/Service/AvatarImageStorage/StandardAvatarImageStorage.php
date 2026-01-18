<?php

namespace Bristolian\Service\AvatarImageStorage;

use Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo;
use Bristolian\Service\ObjectStore\AvatarImageObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class StandardAvatarImageStorage implements AvatarImageStorage
{
    const MINIMUM_AVATAR_SIZE = 512;
    const AVATAR_OUTPUT_SIZE = 512;

    public function __construct(
        private AvatarImageStorageInfoRepo $avatarImageStorageInfoRepo,
        private AvatarImageObjectStore $avatarImageObjectStore,
    ) {
    }

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
    ): string|UploadError {

        $contents = @file_get_contents($uploadedFile->getTmpName());
        if ($contents === false) {
            return UploadError::uploadedFileUnreadable();
        }

        $extension = pathinfo($uploadedFile->getOriginalName(), PATHINFO_EXTENSION);

        // Normalize extension using the file content
        $extension = normalize_file_extension(
            $uploadedFile->getOriginalName(),
            $contents,
            $allowedExtensions
        );

        if ($extension === null) {
            return UploadError::extensionNotAllowed(
                pathinfo($uploadedFile->getOriginalName(), PATHINFO_EXTENSION)
            );
        }

        // Load image with Imagick to check dimensions and resize
        try {
            $image = new \Imagick($uploadedFile->getTmpName());
            
            // Get current dimensions
            $width = $image->getImageWidth();
            $height = $image->getImageHeight();
            
            // Check minimum size
            if ($width < self::MINIMUM_AVATAR_SIZE || $height < self::MINIMUM_AVATAR_SIZE) {
                return UploadError::imageTooSmall($width, $height, self::MINIMUM_AVATAR_SIZE);
            }
            
            // Resize to exactly 512x512 (crops to square first if needed)
            $image->cropThumbnailImage(self::AVATAR_OUTPUT_SIZE, self::AVATAR_OUTPUT_SIZE);
            
            // Convert to JPEG with good quality
            $image->setImageFormat('jpg');
            $image->setImageCompressionQuality(90);
            
            // Get the resized image data
            $contents = $image->getImageBlob();
            $extension = 'jpg';  // Always save as JPG after processing
        } catch (\ImagickException $e) {
            return UploadError::uploadedFileUnreadable();
        }

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . "." . $extension;

        $fileStorageId = $this->avatarImageStorageInfoRepo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        // Upload to object store
        $this->avatarImageObjectStore->upload($normalized_filename, $contents);
        $this->avatarImageStorageInfoRepo->setUploaded($fileStorageId);

        return $fileStorageId;
    }
}
