<?php

namespace Bristolian\CliController;

use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\Service\BristolStairImageStorageProcessor\ObjectStoredFileInfo;
use Bristolian\Service\FileStorageProcessor\UploadError;
use Bristolian\Service\ObjectStore\BristolianStairImageObjectStore;
use Bristolian\Service\ObjectStore\FileObjectStore;
use Ramsey\Uuid\Uuid;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use Bristolian\Service\BristolStairImageStorageProcessor\BristolStairImageStorageProcessor;

class BristolStairs
{
    public function total(BristolStairsRepo $bristolStairsRepo)
    {
        [$flights_of_stairs, $total_steps] = $bristolStairsRepo->get_total_number_of_steps();

        echo "There are $total_steps steps in $flights_of_stairs flights_of_stairs.\n";
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