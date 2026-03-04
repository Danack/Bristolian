<?php

declare(strict_types=1);

namespace Bristolian\Exception\YouTube;

use Bristolian\Exception\BristolianException;

class YouTubeCaptionContentFetchException extends BristolianException
{
    public static function fromUrlFailure(string $message, ?\Throwable $previous = null): self
    {
        return new self('Failed to fetch caption content: ' . $message, 0, $previous);
    }
}
