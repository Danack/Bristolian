<?php

namespace Bristolian\Service\MemeStorage;

use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\FileStorageInfoRepo\FileStorageInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;

//use Bristolian\Repo\FileStorageInfoRepo\FileType;

function get_supported_file_extensions()
{
    return [
        'gif',
        'jpg',
        'jpeg',
        'mp4',
        'png',
        'pdf',
        'webp'
    ];
}

/**
 *
 * Normalizes a supported extension to lower case or returns null if the extension
 * is not supported.
 *
 * @param string $original_name
 * @param string $contents
 * @return array|string|string[]|null
 */
function normalize_file_extension(string $original_name, string $contents)
{
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);

    if (strlen($extension) === 0) {
        return null;
    }

    $supported_file_extensions = get_supported_file_extensions();

    $lower_case_extension = strtolower($extension);

    if (array_contains($lower_case_extension, $supported_file_extensions) === true) {
        return $supported_file_extensions;
    }

    return null;
}

class StandardMemeStorage implements MemeStorage
{
    public function __construct(
        private FileStorageInfoRepo $fileStorageInfoRepo,
        private MemeFilesystem $memeFilesystem
    ) {
    }

    /**
     * @param string $user_id
     * @param string $tmp_path
     * @param int $filesize
     * @param string $original_name
     * @return array{0: true, 1: null}|array{0:false, 1:string}
     */
    public function storeMemeForUser(
        string $user_id,
        UploadedFile $file
    ): array {
        $contents = file_get_contents($file->getTmpName());
        if ($contents === false) {
            return [false, "Failed to read temp uploaded file."];
        }

        // Extension needs to be calculated through a function.
        $extension = normalize_file_extension($file->getName(), $contents);

        [$fileStorageId, $filename] = $this->fileStorageInfoRepo->storeFileInfo(
            $user_id,
            $extension
            //            FileType::Meme
        );

        $this->memeFilesystem->write($filename, $contents);
        $this->fileStorageInfoRepo->setUploaded($fileStorageId);

        return [true, null];
    }
}
