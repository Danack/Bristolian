<?php

namespace BristolianTest\Repo\MemeStorageRepo;

use Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use BristolianTest\Repo\TestPlaceholders;
use Ramsey\Uuid\Uuid;

/**
 * @group db
 */
class PdoMemeStorageRepoFixture extends MemeStorageRepoFixture
{
    use TestPlaceholders;

    public function getTestInstance(): MemeStorageRepo
    {
        return $this->injector->make(PdoMemeStorageRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo
     */
    public function test_createEntry()
    {
        $pdoMemeStorageRepo = $this->make(PdoMemeStorageRepo::class);
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $uuid = Uuid::uuid7();
        $normalized_name = $uuid->toString() . ".jpg";
        $testUser = $this->createTestAdminUser();

//        normalised name  = 019b8e45-7240-73f6-9342-876214f0a99d.jpeg

        $file_id = $pdoMemeStorageRepo->storeMeme(
            $testUser->getUserId(),
            $normalized_name,
            $uploadedFile
        );

        $pdoMemeStorageRepo->setUploaded($file_id);
    }
}
