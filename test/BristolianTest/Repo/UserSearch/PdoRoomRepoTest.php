<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomRepo;

use Bristolian\Repo\RoomRepo\PdoRoomRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use BristolianTest\Support\HasTestWorld;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomRepoTest extends RoomRepoFixture
{
    use HasTestWorld;

    public function getTestInstance(): RoomRepo
    {
        return $this->injector->make(PdoRoomRepo::class);
    }

    protected function getValidUserId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getTestingUserId();
    }
}
