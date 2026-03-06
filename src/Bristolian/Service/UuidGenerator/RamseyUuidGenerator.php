<?php

declare(strict_types = 1);

namespace Bristolian\Service\UuidGenerator;

use Ramsey\Uuid\Uuid;

/**
 * Standard implementation using Ramsey UUID v7.
 */
class RamseyUuidGenerator implements UuidGenerator
{
    public function generate(): string
    {
        return Uuid::uuid7()->toString();
    }
}
