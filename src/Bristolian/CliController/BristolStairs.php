<?php

namespace Bristolian\CliController;

use Bristolian\Filesystem\BristolStairsFilesystem;
use Bristolian\Parameters\BristolStairsGpsParams;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage;
use Bristolian\Service\BristolStairImageStorage\UploadError;
use Bristolian\Service\CliOutput\CliOutput;
use Bristolian\UploadedFiles\UploadedFile;

class BristolStairs
{
    public function __construct(
        private CliOutput $cliOutput
    ) {
    }

    public function total(BristolStairsRepo $bristolStairsRepo): void
    {
        [$flights_of_stairs, $total_steps] = $bristolStairsRepo->get_total_number_of_steps();

        $this->cliOutput->write("There are $total_steps steps in $flights_of_stairs flights_of_stairs.\n");
    }

    public function check_contents(
        BristolStairImageStorageInfoRepo $bristolStairImageStorageInfoRepo,
        BristolStairsFilesystem $bristolStairsFilesystem
    ): void {
        $result = $bristolStairsFilesystem->listContents("");

        $files_in_storage = [];

        try {
            foreach ($result as $file) {
                $files_in_storage[] = $file->path();
            }
        } catch (\League\Flysystem\UnableToListContents $exception) {
            $this->cliOutput->write("Failed to list files in storage in " . __FILE__ . ":" . __LINE__ . ".\n");
            $this->cliOutput->write($exception->getMessage());
            $this->cliOutput->write("\n");
            $this->cliOutput->exit(-1);
        }

        $known_files = [];
        $unknown_files = [];

        foreach ($files_in_storage as $file_in_storage) {
            $result = $bristolStairImageStorageInfoRepo->getByNormalizedName($file_in_storage);

            if ($result === null) {
                $unknown_files[] = $file_in_storage;
            } else {
                $known_files[] = $file_in_storage;
            }
        }

        $this->cliOutput->write("Unknown files:\n");
        $this->cliOutput->write(var_export($unknown_files, true));

        // This code doesn't have permissions. Think I'm going to leave
        // it here like this for now, as I don't want to setup permissions
        // to delete just yet.
//        foreach ($unknown_files as $unknown_file) {
//            $bristolStairsFilesystem->delete($unknown_file);
//        }
    }

    public function create(
        AdminRepo $adminRepo,
        BristolStairImageStorage $bristolStairImageStorage,
        string $image_filename
    ): void {
        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());
        if ($user_id === null) {
            $this->cliOutput->write("Failed to find admin user");
            $this->cliOutput->exit(-1);
        }

        $uploadedFile = UploadedFile::fromFile($image_filename);

        $gpsParams = new BristolStairsGpsParams(null, null);
        $stairInfoOrError = $bristolStairImageStorage->storeFileForUser(
            $user_id,
            $uploadedFile,
            get_supported_bristolian_stair_image_extensions(),
            $gpsParams
        );

        if ($stairInfoOrError instanceof UploadError) {
            $this->cliOutput->write("Failed to upload file");
            $this->cliOutput->exit(-1);
        }
    }
}
