<?php

namespace Bristolian\Repo\VideoRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\video as videoTable;
use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\Video;

interface VideoRepo
{
    #[WritesTable(videoTable::class)]
    public function create(string $user_id, string $youtube_video_id): string;

    /**
     * @throws ContentNotFoundException if video not found
     */
    #[ReadsTable(videoTable::class)]
    public function getById(string $video_id): Video;
}
