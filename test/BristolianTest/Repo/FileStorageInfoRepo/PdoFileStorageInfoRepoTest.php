<?php

namespace BristolianTest\Repo\FileStorageInfoRepo;

use Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use Ramsey\Uuid\Uuid;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @group db
 */

#[CoversClass(\Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo::class)]

class PdoFileStorageInfoRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    public function test_createEntry()
    {
        $pdoFileStorageInfoRepo = $this->make(PdoRoomFileObjectInfoRepo::class);
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $uuid = Uuid::uuid7();
        $normalized_name = $uuid->toString() . ".pdf";
        $original_name = $this->getTestFileName();
        $testUser = $this->createTestAdminUser();

        $file_id = $pdoFileStorageInfoRepo->createRoomFileObjectInfo(
            $testUser->getUserId(),
            $normalized_name,
            $uploadedFile
        );

        $pdoFileStorageInfoRepo->setRoomFileObjectUploaded($file_id);
    }
}
