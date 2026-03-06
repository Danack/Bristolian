<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileObjectInfoRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo;
use Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\Service\UuidGenerator\FixedUuidGenerator;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomFileObjectInfoRepoTest extends RoomFileObjectInfoRepoFixture
{
    public function getTestInstance(): RoomFileObjectInfoRepo
    {
        return $this->injector->make(PdoRoomFileObjectInfoRepo::class);
    }

    protected function getTestUserId(): string
    {
        $adminUser = $this->createTestAdminUser();
        return $adminUser->getUserId();
    }

    /**
     * Duplicate id triggers constraint violation (23000); repo throws UserConstraintFailedException.
     *
     * @covers \Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo::createRoomFileObjectInfo
     */
    public function test_createRoomFileObjectInfo_throws_UserConstraintFailedException_on_duplicate_id(): void
    {
        $fixedUuid = Uuid::uuid7()->toString();
        $pdoSimple = $this->injector->make(PdoSimple::class);
        $uuidGenerator = new FixedUuidGenerator($fixedUuid);
        $repo = new PdoRoomFileObjectInfoRepo($pdoSimple, $uuidGenerator);

        $userId = $this->getTestUserId();
        $uploadedFile = new UploadedFile('/tmp/test.txt', 128, 'test.txt', 0);

        $repo->createRoomFileObjectInfo($userId, 'first.txt', $uploadedFile);

        $this->expectException(UserConstraintFailedException::class);
        $repo->createRoomFileObjectInfo($userId, 'second.txt', $uploadedFile);
    }
}
