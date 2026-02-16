<?php

declare(strict_types = 1);

namespace BristolianTest\Support;

use Bristolian\Repo\AdminRepo\PdoAdminRepo;
use Bristolian\Repo\RoomFileObjectInfoRepo\PdoRoomFileObjectInfoRepo;
use Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo;
use Bristolian\Repo\RoomRepo\PdoRoomRepo;
use Bristolian\Repo\RoomSourceLinkRepo\PdoRoomSourceLinkRepo;
use DI\Injector;

/**
 * TestWorld manages shared test state and provides access to repositories.
 *
 * This follows the pattern described in docs/refactoring/testing_scenarios.md
 * where each bounded context has its own world that provides access to
 * repositories and shared test data.
 *
 * Uses PDO implementations directly so we don't depend on injector aliases.
 *
 * @coversNothing
 */
final class TestWorld
{
    private PdoAdminRepo $adminRepo;
    private PdoRoomRepo $roomRepo;
    private PdoRoomFileObjectInfoRepo $roomFileObjectInfoRepo;
    private PdoRoomFileRepo $roomFileRepo;
    private PdoRoomSourceLinkRepo $roomSourceLinkRepo;

    public function __construct(
        private Injector $injector
    ) {
        $this->adminRepo = $this->injector->make(PdoAdminRepo::class);
        $this->roomRepo = $this->injector->make(PdoRoomRepo::class);
        $this->roomFileObjectInfoRepo = $this->injector->make(PdoRoomFileObjectInfoRepo::class);
        $this->roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);
        $this->roomSourceLinkRepo = $this->injector->make(PdoRoomSourceLinkRepo::class);
    }

    public function adminRepo(): PdoAdminRepo
    {
        return $this->adminRepo;
    }

    public function roomRepo(): PdoRoomRepo
    {
        return $this->roomRepo;
    }

    public function roomFileObjectInfoRepo(): PdoRoomFileObjectInfoRepo
    {
        return $this->roomFileObjectInfoRepo;
    }

    public function roomFileRepo(): PdoRoomFileRepo
    {
        return $this->roomFileRepo;
    }

    public function roomSourceLinkRepo(): PdoRoomSourceLinkRepo
    {
        return $this->roomSourceLinkRepo;
    }

    /**
     * Find a user by email address.
     * Returns null if the user doesn't exist.
     */
    public function findUserByEmail(string $email): ?string
    {
        return $this->adminRepo->getAdminUserId($email);
    }

    /**
     * Find a room by name.
     * Returns null if the room doesn't exist.
     */
    public function findRoomByName(string $name): ?\Bristolian\Model\Generated\Room
    {
        $allRooms = $this->roomRepo->getAllRooms();
        foreach ($allRooms as $room) {
            if ($room->name === $name) {
                return $room;
            }
        }
        return null;
    }
}
