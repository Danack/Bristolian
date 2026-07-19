<?php

declare(strict_types=1);

namespace Bristolian\Repo\WhatDoTheyKnowRequestEventRepo;

use Bristolian\Attribute\WritesTable;
use Bristolian\Database\whatdotheyknow_request_event;

interface WhatDoTheyKnowRequestEventRepo
{
    /**
     * Insert one feed row. Returns false if `wdt_event_id` already exists (unique violation).
     */
    #[WritesTable(whatdotheyknow_request_event::class)]
    public function insertNewRequestEvent(
        int $wdtEventId,
        string $wdtEventPayloadJson,
        int $wdtInfoRequestId,
        string $wdtInfoRequestUrlTitle,
        int $wdtUserId,
        string $wdtUserUrlName,
        string $wdtUserDisplayName,
        int $wdtPublicBodyId,
        \DateTimeImmutable $wdtEventOccurredAtUtc
    ): bool;
}
