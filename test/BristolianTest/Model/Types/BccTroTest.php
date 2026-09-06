<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Types;

use Bristolian\Model\Types\ApiToken;
use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use Bristolian\Repo\BccTroRepo\FakeBccTroRepo;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class BccTroTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\ApiToken
     */
    public function test_construct(): void
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

        $string = $tro->toString();

    }
}
