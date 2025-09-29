<?php

namespace Bristolian\Service\BristolStairImageStorage;

use Bristolian\Model\BristolStairInfo;
use Bristolian\Parameters\BristolStairsGpsParams;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use Bristolian\Service\ObjectStore\BristolianStairImageObjectStore;
use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class StandardBristolStairImageStorage implements BristolStairImageStorage
{
    public function __construct(
        private BristolStairImageStorageInfoRepo $stairImageStorageInfoRepo,
        private BristolianStairImageObjectStore  $bristolianStairImageObjectStore,
        private BristolStairsRepo                $bristolStairsRepo,
    ) {
    }

    /**
     * @param string $user_id
     * @param UploadedFile $uploadedFile
     * @param string[] $allowedExtensions
     * @return string|UploadError
     * @throws \Bristolian\Exception\BristolianException
     */
    public function storeFileForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions,
        BristolStairsGpsParams $gpsParams
    ): BristolStairInfo|UploadError {

        $contents = @file_get_contents($uploadedFile->getTmpName());
        if ($contents === false) {
            return UploadError::uploadedFileUnreadable();
        }

//        $image_filename = $uploadedFile->getTmpName();

//        $filename_for_content = $uploadedFile->getTmpName();
        $filename_for_extension = $uploadedFile->getOriginalName();

        $extension = pathinfo($uploadedFile->getOriginalName(), PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        if ($extension === 'heic') {
            $image = new \Imagick($uploadedFile->getTmpName());

            $image->setImageFormat("jpg");
            $image->setImageCompressionQuality(95);

            $temp_file = tempnam(sys_get_temp_dir(), 'stair_image');

            $image->setImageFormat('jpg');

            $temp_file_with_extension = $temp_file . ".jpg";
            $image->writeImage($temp_file_with_extension);

//            $filename_for_content = $temp_file_with_extension;
            $filename_for_extension = $temp_file_with_extension;

            // This is duplication of start of function, and even more memory.
            $uploadedFile = UploadedFile::fromFile($temp_file_with_extension);
            $contents = @file_get_contents($uploadedFile->getTmpName());
            if ($contents === false) {
                return UploadError::uploadedFileUnreadable();
            }
        }


        // Default to the centre of Bristol
        $latitude = self::BRISTOL_CENTRE_LATITUDE;
        $longitude = self::BRISTOL_CENTRE_LONGITUDE;

        if ($gpsParams->latitude !== null) {
            $latitude = $gpsParams->latitude;
        }
        if ($gpsParams->longitude !== null) {
            $longitude = $gpsParams->longitude;
            \error_log("Using param longitude");
        }

        $coordinates = \get_image_gps($uploadedFile->getTmpName());
        if ($coordinates !== null) {
            $latitude = $coordinates[0];
            $longitude = $coordinates[1];
        }

        // Normalize extension.
        $extension = normalize_file_extension(
            $filename_for_extension, // may have been changed when converting from HEIC to JPG
            $contents,
            $allowedExtensions
        );

        if ($extension === null) {
            return UploadError::unsupportedFileType();
        }

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . "." . $extension;

        $fileStorageId = $this->stairImageStorageInfoRepo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        // TODO - change to stream copying to avoid large memory use.
        $this->bristolianStairImageObjectStore->upload($normalized_filename, $contents);
        $this->stairImageStorageInfoRepo->setUploaded($fileStorageId);

        return $this->bristolStairsRepo->store_stairs_info(
            $fileStorageId,
            $description = "",
            $latitude,
            $longitude,
            $steps = 0,
        );
    }
}
