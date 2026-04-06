<?php

declare(strict_types=1);

namespace Bristolian\Repo\WhatDoTheyKnowRequestEventRepo;

interface WhatDoTheyKnowRequestEventRepo
{
    /**
     * Insert one feed row. Returns false if `wdt_event_id` already exists (unique violation).
     */
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
