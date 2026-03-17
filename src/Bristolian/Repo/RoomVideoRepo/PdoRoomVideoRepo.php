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
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoRoomVideoRepo implements RoomVideoRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * Return all room videos for a room, ordered by created_at desc, limited.
     *
     * @return RoomVideo[]
     */
    public function getVideosForRoom(string $room_id, RoomContentSearchParams $search): array
    {
        $where = ['room_id = :room_id'];
        $params = [
            'room_id' => $room_id,
            'limit' => $search->getLimit(),
        ];

        if ($search->title !== null && $search->title !== '') {
            $where[] = 'title LIKE :title_pattern';
            $params['title_pattern'] = '%' . str_replace(['%', '_'], ['\%', '\_'], $search->title) . '%';
        }
        $createdAtAfter = $search->getCreatedAtAfterForSql();
        if ($createdAtAfter !== null) {
            $where[] = 'created_at >= :created_at_after';
            $params['created_at_after'] = $createdAtAfter;
        }
        $createdAtBefore = $search->getCreatedAtBeforeForSql();
        if ($createdAtBefore !== null) {
            $where[] = 'created_at <= :created_at_before';
            $params['created_at_before'] = $createdAtBefore;
        }
        $documentTimestampAfter = $search->getDocumentTimestampAfterForSql();
        if ($documentTimestampAfter !== null) {
            $where[] = 'document_timestamp >= :document_timestamp_after';
            $params['document_timestamp_after'] = $documentTimestampAfter;
        }
        $documentTimestampBefore = $search->getDocumentTimestampBeforeForSql();
        if ($documentTimestampBefore !== null) {
            $where[] = 'document_timestamp <= :document_timestamp_before';
            $params['document_timestamp_before'] = $documentTimestampBefore;
        }

        $tagIds = $search->getTagIds();
        if (count($tagIds) > 0) {
            $placeholders = [];
            foreach ($tagIds as $index => $tagId) {
                $key = ':tag_id_' . $index;
                $placeholders[] = $key;
                $params[$key] = $tagId;
            }
            $params[':tag_count'] = count($tagIds);
            $where[] = 'id IN (SELECT room_video_id FROM room_video_tag WHERE tag_id IN (' . implode(', ', $placeholders) . ') GROUP BY room_video_id HAVING COUNT(DISTINCT tag_id) = :tag_count)';
        }

        $whereClause = implode(' and ', $where);
        $sql = room_video::SELECT . " where {$whereClause} order by created_at desc limit :limit";
        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            RoomVideo::class
        );
    }

    /**
     * Return room videos for a room with tags and youtube_video_id attached (composite list for API).
     *
     * @return RoomVideoWithTags[]
     */
    public function getVideosForRoomWithTags(string $room_id, RoomContentSearchParams $search): array
    {
        $videos = $this->getVideosForRoom($room_id, $search);
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
                $roomVideo->start_seconds,
                $roomVideo->end_seconds,
                $roomVideo->created_at,
                $roomVideo->document_timestamp,
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
    public function fetchTagIdsForRoomVideo(string $room_video_id): array
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
    public function resolveTagIdsToTags(array $tagIds, array $roomTagsById): array
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
    public function fetchVideoById(string $video_id): Video
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
            'start_seconds' => null,
            'end_seconds' => null,
            'document_timestamp' => null,
        ]);
        return $this->getRoomVideo($id);
    }

    /**
     * Add a clip (time-bounded segment) of an existing room video to the room.
     */
    public function addClip(
        string $room_id,
        string $video_id,
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
            'start_seconds' => $start_seconds,
            'end_seconds' => $end_seconds,
            'document_timestamp' => null,
        ]);
        return $this->getRoomVideo($id);
    }

    /**
     * Update a room video's title and/or description. Null means leave unchanged.
     */
    public function updateTitleAndDescription(
        string $room_id,
        string $room_video_id,
        ?string $title,
        ?string $description
    ): void {
        $roomVideo = $this->getRoomVideoForRoom($room_id, $room_video_id);
        $newTitle = $title !== null ? $title : $roomVideo->title;
        $newDescription = $description !== null ? $description : $roomVideo->description;
        $sql = 'UPDATE room_video SET title = :title, description = :description WHERE id = :id AND room_id = :room_id';
        $this->pdoSimple->execute($sql, [
            'title' => $newTitle,
            'description' => $newDescription,
            'id' => $room_video_id,
            'room_id' => $room_id,
        ]);
    }
}
