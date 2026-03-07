<?php

namespace Bristolian\Repo\RoomVideoTranscriptRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomVideoTranscript;
use Bristolian\Model\Types\RoomVideoTranscriptList;
use Ramsey\Uuid\Uuid;

class InMemoryRoomVideoTranscriptRepo implements RoomVideoTranscriptRepo
{
    /** @var array<string, RoomVideoTranscript> keyed by transcript id */
    private array $transcriptsById = [];

    /** @var array<string, RoomVideoTranscript[]> keyed by room_video_id */
    private array $transcriptsByRoomVideo = [];

    public function getTranscriptsForRoomVideo(string $room_video_id): RoomVideoTranscriptList
    {
        $transcripts = $this->transcriptsByRoomVideo[$room_video_id] ?? [];
        usort($transcripts, fn ($a, $b) => $a->transcript_number <=> $b->transcript_number);
        return new RoomVideoTranscriptList($transcripts);
    }

    public function addTranscript(
        string $room_video_id,
        ?string $language,
        string $vtt_content
    ): string {
        $existing = $this->transcriptsByRoomVideo[$room_video_id] ?? [];
        $maxNumber = 0;
        foreach ($existing as $transcript) {
            if ($transcript->transcript_number > $maxNumber) {
                $maxNumber = $transcript->transcript_number;
            }
        }

        $id = Uuid::uuid7()->toString();
        $transcript = new RoomVideoTranscript(
            id: $id,
            room_video_id: $room_video_id,
            transcript_number: $maxNumber + 1,
            language: $language,
            vtt_content: $vtt_content,
            created_at: new \DateTimeImmutable()
        );

        $this->transcriptsById[$id] = $transcript;
        $this->transcriptsByRoomVideo[$room_video_id][] = $transcript;

        return $id;
    }

    public function getTranscriptById(string $transcript_id): RoomVideoTranscript
    {
        if (!isset($this->transcriptsById[$transcript_id])) {
            throw ContentNotFoundException::transcript_not_found($transcript_id);
        }
        return $this->transcriptsById[$transcript_id];
    }
}
