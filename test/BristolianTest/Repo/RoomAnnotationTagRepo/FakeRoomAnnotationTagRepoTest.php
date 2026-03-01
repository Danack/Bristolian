<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomAnnotationTagRepo;

use Bristolian\Repo\RoomAnnotationTagRepo\FakeRoomAnnotationTagRepo;
use Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo;

/**
 * @coversNothing
 */
class FakeRoomAnnotationTagRepoTest extends RoomAnnotationTagRepoFixture
{
    public function getTestInstance(): RoomAnnotationTagRepo
    {
        return new FakeRoomAnnotationTagRepo();
    }
}
