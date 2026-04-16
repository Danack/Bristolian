<?php

declare(strict_types=1);

namespace BristolianTest\Repo\WhatDoTheyKnowRequestEventRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\PdoSimple\PdoSimpleWithPreviousException;
use Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\PdoWhatDoTheyKnowRequestEventRepo;
use Bristolian\Service\UuidGenerator\UuidGenerator;
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

    /**
     * @covers \Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\PdoWhatDoTheyKnowRequestEventRepo::insertNewRequestEvent
     */
    public function test_insertNewRequestEvent_rethrows_non_duplicate_pdo_simple_exception(): void
    {
        $pdoException = new \PDOException('synthetic failure');
        $pdoException->errorInfo = ['42000', 1064, 'synthetic failure'];
        $expectedException = PdoSimpleWithPreviousException::errorExecutingSql($pdoException);
        $pdo = $this->injector->make(\PDO::class);
        $uuidGenerator = $this->injector->make(UuidGenerator::class);

        $pdoSimple = new class($pdo, $uuidGenerator, $expectedException) extends PdoSimple {
            public function __construct(
                \PDO $pdo,
                UuidGenerator $uuidGenerator,
                private PdoSimpleWithPreviousException $expectedException
            )
            {
                parent::__construct($pdo, $uuidGenerator);
            }

            public function insert(string $query, array $params): int
            {
                throw $this->expectedException;
            }
        };

        $repo = new PdoWhatDoTheyKnowRequestEventRepo($pdoSimple);

        $this->expectExceptionObject($expectedException);

        $repo->insertNewRequestEvent(
            wdtEventId: 123,
            wdtEventPayloadJson: '{"synthetic":true}',
            wdtInfoRequestId: 456,
            wdtInfoRequestUrlTitle: 'synthetic_title',
            wdtUserId: 789,
            wdtUserUrlName: 'synthetic_user',
            wdtUserDisplayName: 'Synthetic User',
            wdtPublicBodyId: 1011,
            wdtEventOccurredAtUtc: new \DateTimeImmutable('2026-01-15 12:00:00', new \DateTimeZone('UTC'))
        );
    }
}
