<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\BccTroRepo\FakeBccTroRepo;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @group standard_repo
 */
#[CoversNothing]
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

    /**
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveDataIfNew
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveData
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::getSavedBatches
     */
    public function test_fake_saveDataIfNew_does_not_append_duplicate_batch(): void
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

        $firstId = $repo->saveDataIfNew([$tro]);
        $secondId = $repo->saveDataIfNew([$tro]);

        $this->assertSame(0, $firstId);
        $this->assertNull($secondId);
        $this->assertCount(1, $repo->getSavedBatches());
    }
}
