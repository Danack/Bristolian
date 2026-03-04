<?php

namespace Bristolian\Repo\VideoRepo;

use Bristolian\Database\video as videoTable;
use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\Video;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoVideoRepo implements VideoRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    public function create(string $user_id, string $youtube_video_id): string
    {
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $this->pdoSimple->insert(videoTable::INSERT, [
            'id' => $id,
            'user_id' => $user_id,
            'youtube_video_id' => $youtube_video_id,
        ]);
        return $id;
    }

    public function getById(string $video_id): Video
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
}
