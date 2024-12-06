<?php

namespace BristolianTest\Repo\FileStorageInfoRepo;

use Bristolian\Model\Meme;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

use Bristolian\Repo\FileStorageInfoRepo\PdoFileStorageInfoRepo;
use BristolianTest\Repo\TestPlaceholders;
use Ramsey\Uuid\Uuid;

/**
 * @coversNothing
 */
class PdoFileStorageInfoRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\FileStorageInfoRepo\PdoFileStorageInfoRepo
     */
    public function test_createEntry()
    {
        $pdoFileStorageInfoRepo = $this->make(PdoFileStorageInfoRepo::class);
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $uuid = Uuid::uuid7();
        $normalized_name = $uuid->toString() . ".pdf";
        $original_name = $this->getTestFileName();
        $testUser = $this->createTestAdminUser();

        $file_id = $pdoFileStorageInfoRepo->storeFileInfo(
            $testUser->getUserId(),
            $normalized_name,
            $uploadedFile
        );

        $pdoFileStorageInfoRepo->setUploaded($file_id);
    }
}
