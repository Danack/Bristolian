<?php

declare(strict_types=1);

namespace Bristolian\Service\YouTube;

class FakeYouTubeTranscriptFetcher implements TranscriptFetcher
{
    /** @var array<string, array{0: string, 1: string|null}> keyed by video id */
    private array $transcripts = [];

    public function addTranscript(string $videoId, string $vttContent, ?string $language = 'en'): void
    {
        $this->transcripts[$videoId] = [$vttContent, $language];
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    public function fetchAsVtt(string $youtubeVideoId): array
    {
        if (!isset($this->transcripts[$youtubeVideoId])) {
            return ["WEBVTT\n\n00:00:00.000 --> 00:00:01.000\n(No captions)\n", null];
        }

        return $this->transcripts[$youtubeVideoId];
    }
}
