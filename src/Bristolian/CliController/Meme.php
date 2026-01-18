<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;

class Meme
{
    public function check_contents(
        MemeStorageRepo $memeStorageRepo,
        MemeFilesystem $memeFilesystem
    ): void {
        $result = $memeFilesystem->listContents("");

        $files_in_storage = [];

        try {
            foreach ($result as $file) {
                $files_in_storage[] = $file->path();
            }
        }
        catch (\League\Flysystem\UnableToListContents $exception) {
            echo "Failed to list files in storage in " . __FILE__ . ":" . __LINE__ .".\n";
            echo $exception->getMessage();
            echo "\n";
            exit(-1);
        }

        $known_files = [];
        $unknown_files = [];

        foreach ($files_in_storage as $file_in_storage) {
            $result = $memeStorageRepo->getByNormalizedName($file_in_storage);

            if ($result === null) {
                $unknown_files[] = $file_in_storage;
            }
            else {
                $known_files[] = $file_in_storage;
            }
        }

        echo "Unknown files:\n";
        var_dump($unknown_files);

        // This code doesn't have permissions. Think I'm going to leave
        // it here like this for now, as I don't want to setup permissions
        // to delete just yet.
//        foreach ($unknown_files as $unknown_file) {
//            $memeFilesystem->delete($unknown_file);
//        }
    }
}
