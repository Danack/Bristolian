<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileObjectInfoRepo;

use Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo;
use Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeRoomFileObjectInfoRepoTest extends RoomFileObjectInfoRepoFixture
{
    /**
     * @return RoomFileObjectInfoRepo
     */
    public function getTestInstance(): RoomFileObjectInfoRepo
    {
        return new FakeRoomFileObjectInfoRepo();
    }

    /**
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo::getStoredFileInfo
     */
    public function test_getStoredFileInfo_returns_created_files(): void
    {
        $repo = new FakeRoomFileObjectInfoRepo();
        $repo->createRoomFileObjectInfo('user_1', 'norm.txt', UploadedFile::fromFile(__FILE__));
        $info = $repo->getStoredFileInfo();
        $this->assertCount(1, $info);
    }
}
