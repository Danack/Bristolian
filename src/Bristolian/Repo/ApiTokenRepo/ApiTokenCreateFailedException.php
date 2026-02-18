<?php

declare(strict_types = 1);

namespace Bristolian\Repo\ApiTokenRepo;

/**
 * Thrown when token creation fails after exhausting retries (e.g. unique constraint collisions).
 */
class ApiTokenCreateFailedException extends \Exception
{
    public static function afterMaxRetries(int $maxRetries, ?\Throwable $previous = null): self
    {
        return new self(
            'Failed to create unique API token after ' . $maxRetries . ' attempts',
            0,
            $previous
        );
    }
}
