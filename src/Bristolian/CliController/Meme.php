<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Service\CliOutput\CliOutput;

class Meme
{
    public function __construct(
        private CliOutput $cliOutput
    ) {
    }

    public function check_contents_of_storage(
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
            $this->cliOutput->write("Failed to list files in storage in " . __FILE__ . ":" . __LINE__ . ".\n");
            $this->cliOutput->write($exception->getMessage());
            $this->cliOutput->write("\n");
            $this->cliOutput->exit(-1);
        }

        $unknown_files = [];

        foreach ($files_in_storage as $file_in_storage) {
            $result = $memeStorageRepo->getByNormalizedName($file_in_storage);

            if ($result === null) {
                $unknown_files[] = $file_in_storage;
            }
        }

        $this->cliOutput->write("The files that exist in storage, but not in the database are:\n");
        $this->cliOutput->write(var_export($unknown_files, true));

        // This code doesn't have permissions. Think I'm going to leave
        // it here like this for now, as I don't want to setup permissions
        // to delete just yet.
//        foreach ($unknown_files as $unknown_file) {
//            $memeFilesystem->delete($unknown_file);
//        }
    }

    /**
     * Check for database meme records that don't have a corresponding file in storage (missing files).
     * Marks such records as deleted and echoes progress.
     */
    public function check_contents_of_database(
        MemeStorageRepo $memeStorageRepo,
        MemeFilesystem $memeFilesystem
    ): void {
        $memes = $memeStorageRepo->listAllMemes();

        foreach ($memes as $meme) {
            $this->cliOutput->write('.');
            if ($memeFilesystem->fileExists($meme->normalized_name) === false) {
                $memeStorageRepo->markAsDeleted($meme->id);
                $this->cliOutput->write("\n" . $meme->normalized_name . " is deleted\n");
            }
        }

        $this->cliOutput->write("\n");
    }
}
