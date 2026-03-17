<?php

declare(strict_types=1);

namespace Bristolian\Service\YouTube;

use Bristolian\Service\YouTube\TranscriptFetcher;
use Bristolian\Exception\YouTube\YouTubeNoCaptionTracksException;

/** TranscriptFetcher that throws for testing error path */
final class ThrowingTranscriptFetcher implements TranscriptFetcher
{
    public function fetchAsVtt(string $youtubeVideoId): array
    {
        throw YouTubeNoCaptionTracksException::forVideo($youtubeVideoId);
    }
}
