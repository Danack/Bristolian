<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomTagRepo;

use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use Bristolian\Repo\RoomTagRepo\RoomTagRepo;
use BristolianTest\Support\HasTestWorld;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomTagRepoTest extends RoomTagRepoFixture
{
    use HasTestWorld;

    private ?string $testRoomId = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $room = $this->world()->roomRepo()->createRoom($userId, 'PdoRoomTagRepoTest Room', 'For room tag repo tests');
        $this->testRoomId = $room->id;
    }

    public function getTestInstance(): RoomTagRepo
    {
        return $this->injector->make(PdoRoomTagRepo::class);
    }

    protected function getTestRoomId(): string
    {
        assert($this->testRoomId !== null);
        return $this->testRoomId;
    }
}
