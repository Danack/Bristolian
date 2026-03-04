<?php

namespace Bristolian\Repo\RoomVideoTranscriptRepo;

use Bristolian\Model\Generated\RoomVideoTranscript;
use Bristolian\Model\Types\RoomVideoTranscriptList;

interface RoomVideoTranscriptRepo
{
    /**
     * Return all transcripts for a room video, ordered by transcript_number.
     */
    public function getTranscriptsForRoomVideo(string $room_video_id): RoomVideoTranscriptList;

    /**
     * Insert a transcript; transcript_number is computed via subquery (next per room_video_id).
     * Unique (room_video_id, language) is enforced at DB level.
     */
    public function addTranscript(
        string $room_video_id,
        ?string $language,
        string $vtt_content
    ): string;

    /**
     * @throws \Bristolian\Exception\ContentNotFoundException
     */
    public function getTranscriptById(string $transcript_id): RoomVideoTranscript;
}
