<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileObjectInfoRepo;

use Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * Abstract test class for RoomFileObjectInfoRepo implementations.
 */
abstract class RoomFileObjectInfoRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * Get a test instance of the RoomFileObjectInfoRepo implementation.
     *
     * @return RoomFileObjectInfoRepo
     */
    abstract public function getTestInstance(): RoomFileObjectInfoRepo;

    /**
     * Get a test user ID. Override in PDO tests to create actual user.
     */
    protected function getTestUserId(): string
    {
        return 'user_123';
    }

    public function test_createRoomFileObjectInfo(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $normalized_filename = 'test_file_' . time() . '.txt';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->createRoomFileObjectInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $this->assertNotEmpty($file_id);
        $this->assertIsString($file_id);
    }

    public function test_setRoomFileObjectUploaded(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $normalized_filename = 'test_file_' . time() . '.txt';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->createRoomFileObjectInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        // Should not throw an exception
        $repo->setRoomFileObjectUploaded($file_id);
        $this->assertTrue(true);
    }
}
