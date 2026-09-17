<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileObjectInfoRepo;

use Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo;
use Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * @group standard_repo
 */

#[CoversMethod(\Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo::class, 'getStoredFileInfo')]

class FakeRoomFileObjectInfoRepoTest extends RoomFileObjectInfoRepoFixture
{
    /**
     * @return RoomFileObjectInfoRepo
     */
    public function getTestInstance(): RoomFileObjectInfoRepo
    {
        return new FakeRoomFileObjectInfoRepo();
    }

    public function test_getStoredFileInfo_returns_created_files(): void
    {
        $repo = new FakeRoomFileObjectInfoRepo();
        $repo->createRoomFileObjectInfo('user_1', 'norm.txt', UploadedFile::fromFile(__FILE__));
        $info = $repo->getStoredFileInfo();
        $this->assertCount(1, $info);
    }
}
