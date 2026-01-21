<?php

declare(strict_types = 1);

namespace Bristolian\Repo\SourceLinkRepo;

use Ramsey\Uuid\Uuid;

/**
 * Fake implementation of SourceLinkRepo for testing.
 */
class FakeSourceLinkRepo implements SourceLinkRepo
{
    public function addSourceLink(string $user_id, string $title, array $highlights): string
    {
        // Generate a unique ID for the source link
        $uuid = Uuid::uuid7();
        return $uuid->toString();
    }
}