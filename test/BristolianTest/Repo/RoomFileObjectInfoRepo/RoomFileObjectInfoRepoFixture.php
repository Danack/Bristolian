<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileObjectInfoRepo;

use Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * Abstract test class for RoomFileObjectInfoRepo implementations.
 *
 * @coversNothing
 */
abstract class RoomFileObjectInfoRepoFixture extends BaseTestCase
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

    /**
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo::createRoomFileObjectInfo
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo::createRoomFileObjectInfo
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo::__construct
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo::createRoomFileObjectInfo
     */
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
    }

    /**
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo::setRoomFileObjectUploaded
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo::createRoomFileObjectInfo
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo::setRoomFileObjectUploaded
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo::createRoomFileObjectInfo
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo::setRoomFileObjectUploaded
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo::createRoomFileObjectInfo
     */
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
    }
}
