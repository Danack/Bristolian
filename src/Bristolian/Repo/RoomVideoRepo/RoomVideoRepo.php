<?php

namespace Bristolian\Repo\RoomVideoRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomVideo;
use Bristolian\Model\Types\RoomVideoWithTags;

interface RoomVideoRepo
{
    /**
     * @return RoomVideo[]
     */
    public function getVideosForRoom(string $room_id): array;

    /**
     * @return RoomVideoWithTags[]
     */
    public function getVideosForRoomWithTags(string $room_id): array;

    /**
     * Get a room video by id. Assumes valid id; throws if not found.
     *
     * @throws ContentNotFoundException
     */
    public function getRoomVideo(string $room_video_id): RoomVideo;

    /**
     * Get a room video that must belong to the given room.
     *
     * @throws ContentNotFoundException if not found or room_video is in a different room
     */
    public function getRoomVideoForRoom(string $room_id, string $room_video_id): RoomVideo;

    /**
     * Add a full video to the room.
     */
    public function addVideo(
        string $room_id,
        string $video_id,
        ?string $title = null,
        ?string $description = null
    ): RoomVideo;

    /**
     * Add a clip (time-bounded segment) of an existing room video.
     */
    public function addClip(
        string $room_id,
        string $video_id,
        ?string $title,
        ?string $description,
        int $start_seconds,
        int $end_seconds
    ): RoomVideo;
}
