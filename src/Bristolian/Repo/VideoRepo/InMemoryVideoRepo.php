<?php

namespace Bristolian\Repo\VideoRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\Video;
use Ramsey\Uuid\Uuid;

class InMemoryVideoRepo implements VideoRepo
{
    /** @var array<string, Video> */
    private array $videos = [];

    public function create(string $user_id, string $youtube_video_id): string
    {
        $id = Uuid::uuid7()->toString();
        $this->videos[$id] = new Video(
            id: $id,
            user_id: $user_id,
            youtube_video_id: $youtube_video_id,
            created_at: new \DateTimeImmutable()
        );
        return $id;
    }

    public function getById(string $video_id): Video
    {
        if (!isset($this->videos[$video_id])) {
            throw ContentNotFoundException::video_not_found($video_id);
        }
        return $this->videos[$video_id];
    }
}
