<?php

declare(strict_types=1);

namespace Bristolian\Service\YouTube;

use Bristolian\Service\YouTube\TranscriptFetcher;

/** TranscriptFetcher that throws for testing error path */
final class ThrowingTranscriptFetcher implements TranscriptFetcher
{
    public function fetchAsVtt(string $youtubeVideoId): array
    {
        throw new \RuntimeException('Transcript fetch failed');
    }
}
