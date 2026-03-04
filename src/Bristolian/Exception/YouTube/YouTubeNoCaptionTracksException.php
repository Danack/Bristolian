<?php

declare(strict_types=1);

namespace Bristolian\Exception\YouTube;

use Bristolian\Exception\BristolianException;

class YouTubeNoCaptionTracksException extends BristolianException
{
    public static function forVideo(string $youtubeVideoId): self
    {
        return new self('No caption tracks found for this video');
    }
}
