<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileObjectInfoRepo;

use Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo;
use Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo;

/**
 * @group db
 */
class PdoRoomFileObjectInfoRepoTest extends RoomFileObjectInfoRepoTest
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
}
