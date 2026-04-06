<?php

declare(strict_types=1);

namespace BristolianTest\Repo\WhatDoTheyKnowRequestEventRepo;

use Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\FakeWhatDoTheyKnowRequestEventRepo;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
final class FakeWhatDoTheyKnowRequestEventRepoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\FakeWhatDoTheyKnowRequestEventRepo::insertNewRequestEvent
     * @covers \Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\FakeWhatDoTheyKnowRequestEventRepo::getInsertedRows
     */
    public function test_insertNewRequestEvent_tracks_rows_and_rejects_duplicate_wdt_event_id(): void
    {
        $repo = new FakeWhatDoTheyKnowRequestEventRepo();
        $occurredAt = new \DateTimeImmutable('2026-02-01 00:00:00', new \DateTimeZone('UTC'));

        self::assertTrue($repo->insertNewRequestEvent(
            wdtEventId: 100,
            wdtEventPayloadJson: '{}',
            wdtInfoRequestId: 200,
            wdtInfoRequestUrlTitle: 'slug',
            wdtUserId: 300,
            wdtUserUrlName: 'u',
            wdtUserDisplayName: 'U',
            wdtPublicBodyId: 90,
            wdtEventOccurredAtUtc: $occurredAt
        ));

        self::assertFalse($repo->insertNewRequestEvent(
            wdtEventId: 100,
            wdtEventPayloadJson: '{}',
            wdtInfoRequestId: 200,
            wdtInfoRequestUrlTitle: 'slug',
            wdtUserId: 300,
            wdtUserUrlName: 'u',
            wdtUserDisplayName: 'U',
            wdtPublicBodyId: 90,
            wdtEventOccurredAtUtc: $occurredAt
        ));

        self::assertCount(1, $repo->getInsertedRows());
        self::assertSame(100, $repo->getInsertedRows()[0]['wdt_event_id']);
    }
}
