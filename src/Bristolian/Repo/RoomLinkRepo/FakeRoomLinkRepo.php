<?php

namespace Bristolian\Repo\RoomLinkRepo;

use Bristolian\App;
use Bristolian\DataType\LinkParam;
use Bristolian\Model\RoomLink;
use Bristolian\Repo\LinkRepo\LinkRepo;
use Ramsey\Uuid\Uuid;

class FakeRoomLinkRepo implements RoomLinkRepo
{
    /**
     * @var RoomLink[]
     */
    private $roomLinks = [];

    public function __construct(private LinkRepo $linkRepo)
    {
    }

//    public function addLinkToRoom(string $link_id, string $room_id)
//    {
//        $this->roomLinks[] = [$link_id, $room_id];
//    }

    /**
     * @param string $room_id
     * @return string[]
     */
    public function getLinksForRoom(string $room_id)
    {
        $linksForRoom = [];

        foreach ($this->roomLinks as $roomLink) {
            [$link_id, $file_room_id] = $roomLink;
            if ($room_id === $file_room_id) {
                $linksForRoom[] = $link_id;
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
            $link_id,
            $linkParam->url,
            $title = $linkParam->title,
            $description = $linkParam->description,
            $room_id,
            $user_id,
            $created_at = $time->format(App::DATE_TIME_EXACT_FORMAT)
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
