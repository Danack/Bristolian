<?php

namespace Bristolian\Repo\VideoRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\Video;

interface VideoRepo
{
    public function create(string $user_id, string $youtube_video_id): string;

    /**
     * @throws ContentNotFoundException if video not found
     */
    public function getById(string $video_id): Video;
}
