<?php

namespace Bristolian\Repo\RoomVideoRepo;

use Bristolian\Database\room_video;
use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomVideo;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoRoomVideoRepo implements RoomVideoRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * @return RoomVideo[]
     */
    public function getVideosForRoom(string $room_id): array
    {
        $sql = room_video::SELECT . " where room_id = :room_id order by created_at asc";
        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            ['room_id' => $room_id],
            RoomVideo::class
        );
    }

    public function getRoomVideo(string $room_video_id): RoomVideo|null
    {
        $sql = room_video::SELECT . " where id = :id";
        return $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            ['id' => $room_video_id],
            RoomVideo::class
        );
    }

    public function getRoomVideoForRoom(string $room_id, string $room_video_id): RoomVideo
    {
        $roomVideo = $this->getRoomVideo($room_video_id);
        if ($roomVideo === null) {
            throw ContentNotFoundException::room_video_not_found($room_id, $room_video_id);
        }
        if ($roomVideo->room_id !== $room_id) {
            throw ContentNotFoundException::room_video_not_found($room_id, $room_video_id);
        }
        return $roomVideo;
    }

    public function addVideo(
        string $room_id,
        string $video_id,
        ?string $title = null,
        ?string $description = null
    ): RoomVideo {
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $this->pdoSimple->insert(room_video::INSERT, [
            'id' => $id,
            'room_id' => $room_id,
            'video_id' => $video_id,
            'title' => $title,
            'description' => $description,
            'parent_room_video_id' => null,
            'start_seconds' => null,
            'end_seconds' => null,
        ]);
        $roomVideo = $this->getRoomVideo($id);
        assert($roomVideo !== null);
        return $roomVideo;
    }

    public function addClip(
        string $room_id,
        string $video_id,
        string $parent_room_video_id,
        ?string $title,
        ?string $description,
        int $start_seconds,
        int $end_seconds
    ): RoomVideo {
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $this->pdoSimple->insert(room_video::INSERT, [
            'id' => $id,
            'room_id' => $room_id,
            'video_id' => $video_id,
            'title' => $title,
            'description' => $description,
            'parent_room_video_id' => $parent_room_video_id,
            'start_seconds' => $start_seconds,
            'end_seconds' => $end_seconds,
        ]);
        $roomVideo = $this->getRoomVideo($id);
        assert($roomVideo !== null);
        return $roomVideo;
    }
}
