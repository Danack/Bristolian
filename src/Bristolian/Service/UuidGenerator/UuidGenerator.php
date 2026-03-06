<?php

declare(strict_types = 1);

namespace Bristolian\Service\UuidGenerator;

/**
 * Generates a UUID string (e.g. UUIDv7).
 * Implementations may be real (random/time-based) or deterministic (for tests).
 */
interface UuidGenerator
{
    public function generate(): string;
}
