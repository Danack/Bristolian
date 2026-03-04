<?php

namespace Bristolian\Repo\RoomVideoRepo;

use Bristolian\Database\room_tag;
use Bristolian\Database\room_video;
use Bristolian\Database\room_video_tag;
use Bristolian\Database\video as videoTable;
use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomTag;
use Bristolian\Model\Generated\RoomVideo;
use Bristolian\Model\Generated\Video;
use Bristolian\Model\Types\RoomVideoWithTags;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoRoomVideoRepo implements RoomVideoRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * Return all room videos for a room, ordered by created_at.
     *
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

    /**
     * Return room videos for a room with tags and youtube_video_id attached (composite list for API).
     *
     * @return RoomVideoWithTags[]
     */
    public function getVideosForRoomWithTags(string $room_id): array
    {
        $videos = $this->getVideosForRoom($room_id);
        $roomTags = $this->pdoSimple->fetchAllAsObjectConstructor(
            room_tag::SELECT . " where room_id = :room_id",
            ['room_id' => $room_id],
            RoomTag::class
        );
        $roomTagsById = [];
        foreach ($roomTags as $tag) {
            $roomTagsById[$tag->tag_id] = $tag;
        }
        $withTags = [];
        foreach ($videos as $roomVideo) {
            $tagIds = $this->fetchTagIdsForRoomVideo($roomVideo->id);
            $tags = $this->resolveTagIdsToTags($tagIds, $roomTagsById);
            $video = $this->fetchVideoById($roomVideo->video_id);
            $withTags[] = new RoomVideoWithTags(
                $roomVideo->id,
                $roomVideo->room_id,
                $roomVideo->video_id,
                $video->youtube_video_id,
                $roomVideo->title,
                $roomVideo->description,
                $roomVideo->parent_room_video_id,
                $roomVideo->start_seconds,
                $roomVideo->end_seconds,
                $roomVideo->created_at,
                $tags
            );
        }
        return $withTags;
    }

    /**
     * Fetch tag IDs linked to a room video from the room_video_tag junction table.
     *
     * @return string[]
     */
    private function fetchTagIdsForRoomVideo(string $room_video_id): array
    {
        $sql = room_video_tag::SELECT . " where room_video_id = :room_video_id";
        $rows = $this->pdoSimple->fetchAllAsData($sql, ['room_video_id' => $room_video_id]);
        return array_map(fn ($row) => $row['tag_id'], $rows);
    }

    /**
     * Map a list of tag IDs to full RoomTag objects using an index keyed by tag_id.
     *
     * Only IDs that exist in the index are included; missing or stale IDs are skipped so
     * the returned array may be shorter than the input list.
     *
     * @param array<string> $tagIds Tag IDs (e.g. from room_video_tag)
     * @param array<string, RoomTag> $roomTagsById Room tags for the room, keyed by tag_id
     * @return RoomTag[]
     */
    private function resolveTagIdsToTags(array $tagIds, array $roomTagsById): array
    {
        $tags = [];
        foreach ($tagIds as $id) {
            if (array_key_exists($id, $roomTagsById)) {
                $tags[] = $roomTagsById[$id];
            }
        }
        return $tags;
    }

    /**
     * Load the canonical video row by id. Throws if not found.
     *
     * @throws ContentNotFoundException
     */
    private function fetchVideoById(string $video_id): Video
    {
        $sql = videoTable::SELECT . " where id = :id";
        $video = $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            ['id' => $video_id],
            Video::class
        );
        if ($video === null) {
            throw ContentNotFoundException::video_not_found($video_id);
        }
        return $video;
    }

    /**
     * Get a room video by id. Assumes valid id; throws if not found.
     *
     * @throws ContentNotFoundException
     */
    public function getRoomVideo(string $room_video_id): RoomVideo
    {
        $sql = room_video::SELECT . " where id = :id";
        $roomVideo = $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            ['id' => $room_video_id],
            RoomVideo::class
        );
        if ($roomVideo === null) {
            throw ContentNotFoundException::room_video_not_found_by_id($room_video_id);
        }
        return $roomVideo;
    }

    /**
     * Load a room video that must belong to the given room. Throws if not found or wrong room.
     *
     * @throws ContentNotFoundException
     */
    public function getRoomVideoForRoom(string $room_id, string $room_video_id): RoomVideo
    {
        $roomVideo = $this->getRoomVideo($room_video_id);
        if ($roomVideo->room_id !== $room_id) {
            throw ContentNotFoundException::room_video_not_found($room_id, $room_video_id);
        }
        return $roomVideo;
    }

    /**
     * Add a full video (not a clip) to a room.
     */
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
        return $this->getRoomVideo($id);
    }

    /**
     * Add a clip (time-bounded segment) of an existing room video to the room.
     */
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
        return $this->getRoomVideo($id);
    }
}
