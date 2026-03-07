<?php

declare(strict_types=1);

namespace Bristolian\Service\YouTube;

use Bristolian\Exception\YouTube\YouTubeCaptionContentFetchException;
use Bristolian\Exception\YouTube\YouTubeNoCaptionTracksException;
use Bristolian\Exception\YouTube\YouTubeWatchPageFetchException;

interface TranscriptFetcher
{
    /**
     * @return array{0: string, 1: string|null} [vtt_content, language]
     * @throws YouTubeNoCaptionTracksException
     * @throws YouTubeWatchPageFetchException
     * @throws YouTubeCaptionContentFetchException
     */
    public function fetchAsVtt(string $youtubeVideoId): array;
}
