<?php

declare(strict_types=1);

namespace Bristolian\Exception\YouTube;

use Bristolian\Exception\BristolianException;

class YouTubeWatchPageFetchException extends BristolianException
{
    public static function fromUrlFailure(string $message, ?\Throwable $previous = null): self
    {
        return new self('Failed to load YouTube watch page: ' . $message, 0, $previous);
    }
}
