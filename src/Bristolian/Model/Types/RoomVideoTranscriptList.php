<?php

declare(strict_types=1);

namespace Bristolian\Model\Types;

use Bristolian\Model\Generated\RoomVideoTranscript;

/**
 * Immutable list of transcripts for a room video (returned by RoomVideoTranscriptRepo::getTranscriptsForRoomVideo).
 */
final class RoomVideoTranscriptList
{
    /**
     * @param RoomVideoTranscript[] $transcripts
     */
    public function __construct(
        public readonly array $transcripts
    ) {
    }
}
