<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Model\Types\BccTro
 */
class BccTroTest extends BaseTestCase
{
    public function testConstruct(): void
    {
        $statementOfReasons = new BccTroDocument(
            'Statement of Reasons',
            '/files/documents/statement',
            '1001'
        );

        $noticeOfProposal = new BccTroDocument(
            'Notice of Proposal',
            '/files/documents/notice',
            '1002'
        );

        $proposedPlan = new BccTroDocument(
            'Proposed Plan',
            '/files/documents/plan',
            '1003'
        );

        $tro = new BccTro(
            'Test TRO Title',
            'REF-TEST-001',
            $statementOfReasons,
            $noticeOfProposal,
            $proposedPlan
        );

        $this->assertEquals('Test TRO Title', $tro->title);
        $this->assertEquals('REF-TEST-001', $tro->reference_code);
        $this->assertSame($statementOfReasons, $tro->statement_of_reasons);
        $this->assertSame($noticeOfProposal, $tro->notice_of_proposal);
        $this->assertSame($proposedPlan, $tro->proposed_plan);
    }
}
