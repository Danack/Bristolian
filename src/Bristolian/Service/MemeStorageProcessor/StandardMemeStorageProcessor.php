<?php

namespace Bristolian\Service\MemeStorageProcessor;

use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class StandardMemeStorageProcessor implements MemeStorageProcessor
{
    public function __construct(
        private MemeStorageRepo $memeStorageRepo,
    ) {
    }

    /**
     * @param string $user_id
     * @param UploadedFile $uploadedFile
     * @param string[] $allowedExtensions
     * @param FileObjectStore $fileObjectStore
     * @return ObjectStoredMeme|UploadError
     * @throws \Bristolian\Exception\BristolianException
     */
    public function storeMemeForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions,
        FileObjectStore $fileObjectStore
    ): ObjectStoredMeme|UploadError {

        $contents = file_get_contents($uploadedFile->getTmpName());
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

        $meme_id = $this->memeStorageRepo->storeMeme(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        // TODO - change to stream copying to avoid large memory use.
        $fileObjectStore->upload($normalized_filename, $contents);

        $this->memeStorageRepo->setUploaded($meme_id);

        return new ObjectStoredMeme(
            $normalized_filename,
            $meme_id
        );
    }
}
