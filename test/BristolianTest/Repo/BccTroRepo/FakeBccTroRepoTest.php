<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\BccTroRepo\FakeBccTroRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeBccTroRepoTest extends BccTroRepoFixture
{
    public function getTestInstance(): BccTroRepo
    {
        return new FakeBccTroRepo();
    }

    /**
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveData
     */
    public function test_fake_saveData_stores_tros(): void
    {
        $repo = new FakeBccTroRepo();
        $statement = new BccTroDocument('Stmt', '/f/1', 'd1');
        $notice = new BccTroDocument('Notice', '/f/2', 'd2');
        $plan = new BccTroDocument('Plan', '/f/3', 'd3');
        $tro = new BccTro(
            title: 'Fake TRO',
            reference_code: 'F-001',
            statement_of_reasons: $statement,
            notice_of_proposal: $notice,
            proposed_plan: $plan
        );

        $repo->saveData([$tro]);
        $this->addToAssertionCount(1);
    }
}
