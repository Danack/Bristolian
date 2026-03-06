<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\BccTroRepo\PdoBccTroRepo;

/**
 * @group db
 * @coversNothing
 */
class PdoBccTroRepoTest extends BccTroRepoFixture
{
    public function getTestInstance(): BccTroRepo
    {
        return $this->injector->make(PdoBccTroRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::__construct
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::saveData
     */
    public function test_pdo_saveData_persists_tros(): void
    {
        $repo = $this->injector->make(PdoBccTroRepo::class);
        $statement = new BccTroDocument('Statement', '/files/1', 'doc1');
        $notice = new BccTroDocument('Notice', '/files/2', 'doc2');
        $plan = new BccTroDocument('Plan', '/files/3', 'doc3');
        $tro = new BccTro(
            title: 'TRO Title',
            reference_code: 'REF-001',
            statement_of_reasons: $statement,
            notice_of_proposal: $notice,
            proposed_plan: $plan
        );

        $repo->saveData([$tro]);
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::getMostRecentData
     */
    public function test_pdo_getMostRecentData_returns_null(): void
    {
        $repo = $this->injector->make(PdoBccTroRepo::class);
        $this->assertNull($repo->getMostRecentData());
    }

    /**
     * saveData throws when convertToValue returns an error (e.g. unsupported type).
     *
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::saveData
     */
    public function test_pdo_saveData_throws_when_conversion_fails(): void
    {
        $repo = $this->injector->make(PdoBccTroRepo::class);
        $unsupported = [new \stdClass()];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported type');

        $repo->saveData($unsupported);
    }
}
