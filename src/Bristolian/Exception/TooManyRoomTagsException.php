<?php

declare(strict_types = 1);

namespace Bristolian\Exception;

class TooManyRoomTagsException extends BristolianException
{
    public static function forMaxReached(int $max): self
    {
        return new self("Maximum tags per room ($max) reached.");
    }
}
