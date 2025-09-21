<?php

namespace Bristolian\CliController;

use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\Service\BristolStairImageStorageProcessor\ObjectStoredFileInfo;
use Bristolian\Service\BristolStairImageStorageProcessor\UploadError;
use Bristolian\Service\ObjectStore\BristolianStairImageObjectStore;
use Bristolian\Service\ObjectStore\FileObjectStore;
use Ramsey\Uuid\Uuid;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use Bristolian\Service\BristolStairImageStorageProcessor\BristolStairImageStorageProcessor;
use Bristolian\Filesystem\BristolStairsFilesystem;

class BristolStairs
{
    public function total(BristolStairsRepo $bristolStairsRepo)
    {
        [$flights_of_stairs, $total_steps] = $bristolStairsRepo->get_total_number_of_steps();

        echo "There are $total_steps steps in $flights_of_stairs flights_of_stairs.\n";
    }

    public function check_contents(
        BristolStairImageStorageInfoRepo $bristolStairImageStorageInfoRepo,
        BristolStairsFilesystem $bristolStairsFilesystem)
    {
        $result = $bristolStairsFilesystem->listContents("");

        $files_in_storage = [];

        /** @var \League\Flysystem\FileAttributes[] $result */
        foreach ($result as $file) {
            $files_in_storage[] = $file->path();
        }

        $known_files = [];
        $unknown_files = [];

        foreach ($files_in_storage as $file_in_storage) {
            $result = $bristolStairImageStorageInfoRepo->getByNormalizedName($file_in_storage);

            if ($result === null) {
                $unknown_files[] = $file_in_storage;
            }
            else {
                $known_files[] = $file_in_storage;
            }
        }

        echo "Unknown files:\n";
        var_dump($unknown_files);

        // Thise code doesn't have permissions. Think I'm going to leave
        // it here like this for now, as I don't want to setup permissions
        // to delete just yet.
//        foreach ($unknown_files as $unknown_file) {
//            $bristolStairsFilesystem->delete($unknown_file);
//        }
    }

    public function create(
        AdminRepo $adminRepo,
        BristolStairImageStorageProcessor $bristolStairImageStorageProcessor,
        BristolianStairImageObjectStore $bristolStairImageObjectStore,
        BristolStairsRepo $bristolStairsRepo,
        string $image_filename
    ) {
        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());
        if ($user_id === null) {
            echo "Failed to find admin user";
            exit(-1);
        }

        $latitude = 51.4536491;
        $longitude = -2.5913353;


        $extension = pathinfo($image_filename, PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        if ($extension === 'heic') {
            $image = new \Imagick($image_filename);

            $image->setImageFormat("jpg");
            $image->setImageCompressionQuality(95);

            $temp_file = tempnam(sys_get_temp_dir(), 'stair_image');

            $image->setImageFormat('jpg');

            $temp_file_with_extension = $temp_file . ".jpg";
            $image->writeImage($temp_file_with_extension);

            $image_filename = $temp_file_with_extension;
        }

        $coordinates = \get_image_gps($image_filename);

        if ($coordinates !== null) {
            $latitude = $coordinates[0];
            $longitude = $coordinates[1];
        }

        $uploadedFile = UploadedFile::fromFile($image_filename);

        $result = $bristolStairImageStorageProcessor->storeFileForUser(
            $user_id,
            $uploadedFile,
            get_supported_bristolian_stair_image_extensions(),
            $bristolStairImageObjectStore // TODO - why is this not a dependency on the implementation?
        );

        if ($result instanceof UploadError) {
            echo "Failed to upload file";
            exit(-1);
        }

        $bristolStairsRepo->store_stairs_info(
            $result->fileStorageId,
            $description = "",
            $latitude,
            $longitude,
            $steps = 0,
        );
    }
}