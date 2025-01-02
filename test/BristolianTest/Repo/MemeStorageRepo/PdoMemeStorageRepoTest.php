<?php

namespace BristolianTest\Repo\MemeStorageRepo;

use Bristolian\Model\Meme;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

use Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo;
use BristolianTest\Repo\TestPlaceholders;
use Ramsey\Uuid\Uuid;

/**
 * @coversNothing
 */
class PdoMemeStorageRepoTest extends BaseTestCase
{
    use TestPlaceholders;

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

        $file_id = $pdoMemeStorageRepo->storeMeme(
            $testUser->getUserId(),
            $normalized_name,
            $uploadedFile
        );

        $pdoMemeStorageRepo->setUploaded($file_id);
    }
}
