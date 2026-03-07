<?php

namespace Bristolian\Repo\RoomVideoRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomVideo;
use Bristolian\Model\Types\RoomVideoWithTags;
use Bristolian\Repo\RoomTagRepo\RoomTagRepo;
use Bristolian\Repo\RoomVideoTagRepo\RoomVideoTagRepo;
use Bristolian\Repo\VideoRepo\VideoRepo;
use Ramsey\Uuid\Uuid;

class InMemoryRoomVideoRepo implements RoomVideoRepo
{
    /** @var array<string, RoomVideo> keyed by room_video id */
    private array $roomVideos = [];

    public function __construct(
        private RoomVideoTagRepo $roomVideoTagRepo,
        private VideoRepo $videoRepo,
        private RoomTagRepo $roomTagRepo,
    ) {
    }

    /**
     * @return RoomVideo[]
     */
    public function getVideosForRoom(string $room_id): array
    {
        $results = [];
        foreach ($this->roomVideos as $roomVideo) {
            if ($roomVideo->room_id === $room_id) {
                $results[] = $roomVideo;
            }
        }
        usort($results, fn ($a, $b) => $a->created_at <=> $b->created_at);
        return $results;
    }

    /**
     * @return RoomVideoWithTags[]
     */
    public function getVideosForRoomWithTags(string $room_id): array
    {
        $videos = $this->getVideosForRoom($room_id);

        $roomTags = $this->roomTagRepo->getTagsForRoom($room_id);
        $roomTagsById = [];
        foreach ($roomTags as $tag) {
            $roomTagsById[$tag->tag_id] = $tag;
        }

        $withTags = [];
        foreach ($videos as $roomVideo) {
            $tagIds = $this->roomVideoTagRepo->getTagIdsForRoomVideo($roomVideo->id);
            $tags = [];
            foreach ($tagIds as $tagId) {
                if (array_key_exists($tagId, $roomTagsById)) {
                    $tags[] = $roomTagsById[$tagId];
                }
            }
            $video = $this->videoRepo->getById($roomVideo->video_id);
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
                $tags
            );
        }
        return $withTags;
    }

    public function getRoomVideo(string $room_video_id): RoomVideo
    {
        if (!isset($this->roomVideos[$room_video_id])) {
            throw ContentNotFoundException::room_video_not_found_by_id($room_video_id);
        }
        return $this->roomVideos[$room_video_id];
    }

    public function getRoomVideoForRoom(string $room_id, string $room_video_id): RoomVideo
    {
        $roomVideo = $this->getRoomVideo($room_video_id);
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
        $id = Uuid::uuid7()->toString();
        $roomVideo = new RoomVideo(
            id: $id,
            room_id: $room_id,
            video_id: $video_id,
            title: $title,
            description: $description,
            start_seconds: null,
            end_seconds: null,
            created_at: new \DateTimeImmutable()
        );
        $this->roomVideos[$id] = $roomVideo;
        return $roomVideo;
    }

    public function addClip(
        string $room_id,
        string $video_id,
        ?string $title,
        ?string $description,
        int $start_seconds,
        int $end_seconds
    ): RoomVideo {
        $id = Uuid::uuid7()->toString();
        $roomVideo = new RoomVideo(
            id: $id,
            room_id: $room_id,
            video_id: $video_id,
            title: $title,
            description: $description,
            start_seconds: $start_seconds,
            end_seconds: $end_seconds,
            created_at: new \DateTimeImmutable()
        );
        $this->roomVideos[$id] = $roomVideo;
        return $roomVideo;
    }
}
