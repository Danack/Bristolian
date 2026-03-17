<?php

namespace Bristolian\Repo\RoomLinkRepo;

use Bristolian\Model\Generated\RoomLink;
use Bristolian\Parameters\LinkParam;
use Bristolian\Parameters\RoomContentSearchParams;
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

    /**
     * @param string $room_id
     * @return RoomLink[]
     */
    public function getLinksForRoom(string $room_id, RoomContentSearchParams $search): array
    {
        $linksForRoom = [];

        foreach ($this->roomLinks as $roomLink) {
            if ($room_id === $roomLink->room_id) {
                $linksForRoom[] = $roomLink;
            }
        }

        $linksForRoom = $this->filterLinksBySearch($linksForRoom, $search);
        usort($linksForRoom, fn ($a, $b) => $b->created_at <=> $a->created_at);
        return array_slice($linksForRoom, 0, $search->getLimit());
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
            $time,
            null
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

    /**
     * Set document_timestamp for a room link (for testing filter behaviour).
     * Call after addLinkToRoomFromParam; the link must already exist.
     */
    public function setDocumentTimestampForRoomLink(string $room_link_id, \DateTimeInterface $documentTimestamp): void
    {
        foreach ($this->roomLinks as $index => $roomLink) {
            if ($roomLink->id === $room_link_id) {
                $this->roomLinks[$index] = new RoomLink(
                    $roomLink->id,
                    $roomLink->room_id,
                    $roomLink->link_id,
                    $roomLink->title,
                    $roomLink->description,
                    $roomLink->created_at,
                    $documentTimestamp
                );
                return;
            }
        }
    }

    /**
     * @param RoomLink[] $links
     * @return RoomLink[]
     */
    private function filterLinksBySearch(array $links, RoomContentSearchParams $search): array
    {
        return array_filter($links, function (RoomLink $link) use ($search): bool {
            $title = $link->title ?? '';
            if ($search->title !== null && $search->title !== '' && stripos($title, $search->title) === false) {
                return false;
            }
            if ($search->created_at_after !== null && $link->created_at < $search->created_at_after) {
                return false;
            }
            if ($search->created_at_before !== null && $link->created_at > $search->created_at_before) {
                return false;
            }
            if ($search->document_timestamp_after !== null && $link->document_timestamp !== null && $link->document_timestamp < $search->document_timestamp_after) {
                return false;
            }
            if ($search->document_timestamp_before !== null && $link->document_timestamp !== null && $link->document_timestamp > $search->document_timestamp_before) {
                return false;
            }
            return true;
        });
    }
}
