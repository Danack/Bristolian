<?php

namespace Bristolian\Repo\RoomLinkRepo;

use Bristolian\Parameters\LinkParam;
use Bristolian\Repo\LinkRepo\LinkRepo;
use Ramsey\Uuid\Uuid;
use Bristolian\Model\Generated\RoomLink;

class FakeRoomLinkRepo implements RoomLinkRepo
{
    /**
     * @var RoomLink[]
     */
    private $roomLinks = [];

    public function __construct(private LinkRepo $linkRepo)
    {
    }

    /**
     * @param string $room_id
     * @return RoomLink[]
     */
    public function getLinksForRoom(string $room_id): array
    {
        $linksForRoom = [];

        foreach ($this->roomLinks as $roomLink) {
            if ($room_id === $roomLink->room_id) {
                $linksForRoom[] = $roomLink;
            }
        }

        return $linksForRoom;
    }

//    public function getFileDetails(string $room_id, string $file_id): StoredFile|null
//    {
//        // TODO - needs implementing, and probably moving to a separate repo
//        return null;
//    }

    public function addLinkToRoomFromParam(
        string $user_id,
        string $room_id,
        LinkParam $linkParam
    ): string {
        $link_id = $this->linkRepo->store_link($user_id, $linkParam->url);

        $time = new \DateTimeImmutable("2010-01-28T15:00:00+02:00");

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $roomLink = new RoomLink(
            $id,
            $room_id,
            $link_id,
            $linkParam->title,
            $linkParam->description,
            $time
        );

        $this->roomLinks[] = $roomLink;

        return $id;
    }

    public function getRoomLink(string $room_link_id): RoomLink|null
    {
        foreach ($this->roomLinks as $roomLink) {
            if ($roomLink->id === $room_link_id) {
                return $roomLink;
            }
        }
        return null;
    }


    public function getLastAddedLink(): RoomLink|null
    {
        $last = end($this->roomLinks);
        if ($last === false) {
            return null;
        }

        return $last;
    }
}
