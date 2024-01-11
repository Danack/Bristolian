<?php

namespace Bristolian\Service\MemeStorage;

use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\FileStorageRepo\FileStorageInfoRepo;
use Bristolian\Repo\FileStorageRepo\FileType;

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
        string $tmp_path,
        int $filesize,
        string $original_name
    ): array {
        $contents = file_get_contents($tmp_path);
        if ($contents === false) {
            return [false, "Failed to read temp uploaded file."];
        }

        $filename = $user_id . '-' . microtime();

        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        if (strlen($extension) > 0) {
            $filename = $filename . '.' . $extension;
        }

        $fileStorageId = $this->fileStorageInfoRepo->createEntry(
            $user_id,
            $filename,
            FileType::Meme
        );

        $this->memeFilesystem->write($filename, $contents);
        $this->fileStorageInfoRepo->setUploaded($fileStorageId);

        return [true, null];
    }




    // TODO - use stream copying

//      $manager = new Flysystem\MountManager(array(
//    'local'  => $local,
//    'remote' => $remote,
//));
//
//$isCopied = $manager->copy('local://'. $filename, 'remote://test.bin');

//        $stream = $manager->readStream('local://'. $filename);
//        if ( ! $stream) {
//            // handle failure
//        }
//        $manager->writeStream('remote://test.bin', $stream);
}
