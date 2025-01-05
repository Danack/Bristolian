<?php

namespace Bristolian\Service\FileStorageProcessor;

use Bristolian\Repo\FileStorageInfoRepo\FileStorageInfoRepo;
use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class StandardFileStorageProcessor implements FileStorageProcessor
{
    public function __construct(
        private FileStorageInfoRepo $fileStorageInfoRepo,
    ) {
    }

    /**
     * @param string $user_id
     * @param UploadedFile $uploadedFile
     * @param string[] $allowedExtensions
     * @param FileObjectStore $fileObjectStore
     * @return ObjectStoredFileInfo|UploadError
     * @throws \Bristolian\Exception\BristolianException
     */
    public function storeFileForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions,
        FileObjectStore $fileObjectStore
    ): ObjectStoredFileInfo|UploadError {

        $contents = @file_get_contents($uploadedFile->getTmpName());
        if ($contents === false) {
            return UploadError::uploadedFileUnreadable();
        }

        // Normalize extension.
        $extension = normalize_file_extension(
            $uploadedFile->getOriginalName(),
            $contents,
            $allowedExtensions
        );

        if ($extension === null) {
            return UploadError::unsupportedFileType();
        }

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . "." . $extension;

        $fileStorageId = $this->fileStorageInfoRepo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        // TODO - change to stream copying to avoid large memory use.
        $fileObjectStore->upload($normalized_filename, $contents);

        $this->fileStorageInfoRepo->setUploaded($fileStorageId);

        return new ObjectStoredFileInfo(
            $normalized_filename,
            $fileStorageId
        );
    }
}
