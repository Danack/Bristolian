<?php

declare(strict_types = 1);

namespace Bristolian\Service\UuidGenerator;

/**
 * Deterministic implementation that always returns the same UUID.
 * For use in tests when predictable IDs are needed.
 */
class FixedUuidGenerator implements UuidGenerator
{
    public function __construct(
        private string $uuid = '00000000-0000-0000-0000-000000000001'
    ) {
    }

    public function generate(): string
    {
        return $this->uuid;
    }
}
