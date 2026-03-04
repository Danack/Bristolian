<?php

namespace Bristolian\Repo\RoomVideoTranscriptRepo;

use Bristolian\Database\room_video_transcript;
use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomVideoTranscript;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoRoomVideoTranscriptRepo implements RoomVideoTranscriptRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * @return RoomVideoTranscript[]
     */
    public function getTranscriptsForRoomVideo(string $room_video_id): array
    {
        $sql = room_video_transcript::SELECT . " where room_video_id = :room_video_id order by transcript_number asc";
        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            ['room_video_id' => $room_video_id],
            RoomVideoTranscript::class
        );
    }

    public function addTranscript(
        string $room_video_id,
        ?string $language,
        string $vtt_content
    ): string {
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $sql = <<< SQL
insert into room_video_transcript (id, room_video_id, transcript_number, language, vtt_content)
select :id, :room_video_id, sub.next_num, :language, :vtt_content
from (
    select coalesce(max(transcript_number), 0) + 1 as next_num
    from room_video_transcript
    where room_video_id = :room_video_id
) sub
SQL;
        $this->pdoSimple->insert($sql, [
            'id' => $id,
            'room_video_id' => $room_video_id,
            'language' => $language,
            'vtt_content' => $vtt_content,
        ]);
        return $id;
    }

    public function getTranscriptById(string $transcript_id): RoomVideoTranscript
    {
        $sql = room_video_transcript::SELECT . " where id = :id";
        $transcript = $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            ['id' => $transcript_id],
            RoomVideoTranscript::class
        );
        if ($transcript === null) {
            throw ContentNotFoundException::transcript_not_found($transcript_id);
        }
        return $transcript;
    }
}
