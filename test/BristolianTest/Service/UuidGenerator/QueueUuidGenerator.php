<?php

declare(strict_types=1);

namespace BristolianTest\Service\UuidGenerator;

use Bristolian\Service\UuidGenerator\UuidGenerator;

/**
 * Returns UUIDs from a queue in order. For tests that need a sequence of generated ids
 * (e.g. duplicate then success for insertWithUuid).
 */
final class QueueUuidGenerator implements UuidGenerator
{
    /**
     * @param list<string> $uuids
     */
    public function __construct(
        private array $uuids
    ) {
    }

    public function generate(): string
    {
        if ($this->uuids === []) {
            throw new \RuntimeException('QueueUuidGenerator has no more UUIDs.');
        }

        return array_shift($this->uuids);
    }
}
