<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use Bristolian\Model\Types\BccTro;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use BristolianTest\BaseTestCase;

/**
 * @internal
 * @coversNothing
 */
abstract class BccTroRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the BccTroRepo implementation.
     *
     * @return BccTroRepo
     */
    abstract public function getTestInstance(): BccTroRepo;


    /**
     * @covers \Bristolian\Repo\BccTroRepo\BccTroRepo::saveData
     */
    public function test_saveData_stores_data(): void
    {
        $repo = $this->getTestInstance();

        $statement1 = new \Bristolian\Model\Types\BccTroDocument('Statement 1', '/files/1', 'doc1');
        $notice1 = new \Bristolian\Model\Types\BccTroDocument('Notice 1', '/files/2', 'doc2');
        $plan1 = new \Bristolian\Model\Types\BccTroDocument('Plan 1', '/files/3', 'doc3');

        $tro1 = new BccTro(
            title: 'TRO 1',
            reference_code: 'REF-001',
            statement_of_reasons: $statement1,
            notice_of_proposal: $notice1,
            proposed_plan: $plan1
        );

        // Should not throw exception
        $repo->saveData([$tro1]);
    }


    /**
     * @covers \Bristolian\Repo\BccTroRepo\BccTroRepo::saveData
     */
    public function test_saveData_accepts_empty_array(): void
    {
        $repo = $this->getTestInstance();

        // Should not throw exception
        $repo->saveData([]);
    }
}
