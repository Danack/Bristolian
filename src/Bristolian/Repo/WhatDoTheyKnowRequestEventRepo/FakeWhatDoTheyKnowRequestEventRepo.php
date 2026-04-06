<?php

declare(strict_types=1);

namespace Bristolian\Repo\WhatDoTheyKnowRequestEventRepo;

/**
 * In-memory implementation for tests.
 */
class FakeWhatDoTheyKnowRequestEventRepo implements WhatDoTheyKnowRequestEventRepo
{
    /**
     * @var list<array<string, mixed>>
     */
    private array $insertedRows = [];

    /**
     * @var array<int, true>
     */
    private array $wdtEventIdsSeen = [];

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
    ): bool {
        if (array_key_exists($wdtEventId, $this->wdtEventIdsSeen) === true) {
            return false;
        }

        $this->wdtEventIdsSeen[$wdtEventId] = true;
        $this->insertedRows[] = [
            'wdt_event_id' => $wdtEventId,
            'wdt_event_payload_json' => $wdtEventPayloadJson,
            'wdt_info_request_id' => $wdtInfoRequestId,
            'wdt_info_request_url_title' => $wdtInfoRequestUrlTitle,
            'wdt_user_id' => $wdtUserId,
            'wdt_user_url_name' => $wdtUserUrlName,
            'wdt_user_display_name' => $wdtUserDisplayName,
            'wdt_public_body_id' => $wdtPublicBodyId,
            'wdt_event_occurred_at_utc' => $wdtEventOccurredAtUtc,
        ];

        return true;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getInsertedRows(): array
    {
        return $this->insertedRows;
    }
}
