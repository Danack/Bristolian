<?php

declare(strict_types=1);

namespace BristolianTest\Repo\WhatDoTheyKnowRequestEventRepo;

use Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\PdoWhatDoTheyKnowRequestEventRepo;
use BristolianTest\BaseTestCase;

/**
 * @group db
 * @coversNothing
 */
final class PdoWhatDoTheyKnowRequestEventRepoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\PdoWhatDoTheyKnowRequestEventRepo::__construct
     * @covers \Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\PdoWhatDoTheyKnowRequestEventRepo::insertNewRequestEvent
     */
    public function test_insertNewRequestEvent_returns_true_then_false_on_duplicate_wdt_event_id(): void
    {
        $repo = $this->injector->make(PdoWhatDoTheyKnowRequestEventRepo::class);
        $wdtEventId = random_int(900_000_000, 1_900_000_000);
        $occurredAt = new \DateTimeImmutable('2026-01-15 12:00:00', new \DateTimeZone('UTC'));
        $payload = '{"synthetic":true}';

        $insertedFirst = $repo->insertNewRequestEvent(
            wdtEventId: $wdtEventId,
            wdtEventPayloadJson: $payload,
            wdtInfoRequestId: 9_999_001,
            wdtInfoRequestUrlTitle: 'synthetic_test_slug',
            wdtUserId: 9_999_002,
            wdtUserUrlName: 'synthetic_user',
            wdtUserDisplayName: 'Synthetic User',
            wdtPublicBodyId: 90,
            wdtEventOccurredAtUtc: $occurredAt
        );

        self::assertTrue($insertedFirst);

        $insertedSecond = $repo->insertNewRequestEvent(
            wdtEventId: $wdtEventId,
            wdtEventPayloadJson: $payload,
            wdtInfoRequestId: 9_999_001,
            wdtInfoRequestUrlTitle: 'synthetic_test_slug',
            wdtUserId: 9_999_002,
            wdtUserUrlName: 'synthetic_user',
            wdtUserDisplayName: 'Synthetic User',
            wdtPublicBodyId: 90,
            wdtEventOccurredAtUtc: $occurredAt
        );

        self::assertFalse($insertedSecond);
    }
}
