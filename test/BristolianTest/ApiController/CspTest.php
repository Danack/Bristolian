<?php

namespace BristolianTest\ApiController;

use Bristolian\ApiController\Csp;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;
use Bristolian\CSPViolation\CSPViolationStorage;
use Bristolian\CSPViolation\FakeCSPViolationStorage;

/**
 * @covers \Bristolian\ApiController\Csp
 */
class CspTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks()
    {
        $csp = new Csp();

        $varMap = new ArrayVarMap([]);
        $cspStorage = new FakeCSPViolationStorage();

        for ($i = 0; $i < 100; $i++) {
            $cspReport = $this->createContentPolicyViolationReport(['line-number' => 100 + $i]);
            $cspStorage->report($cspReport);
        }

        $result = $csp->get_reports_for_page($varMap, $cspStorage);


        $this->assertSame([0], $cspStorage->getPagesRequested());

        $data = json_decode_safe($result->getBody());
        $this->assertArrayHasKey("reports", $data);
        $reports = $data["reports"];
        $this->assertCount(CSPViolationStorage::REPORTS_PER_PAGE, $reports);

        $countdown = 199;
        foreach ($reports as $report) {
            $this->assertArrayHasKey("line-number", $report);
            $line_number = $report["line-number"];

            $this->assertEquals($countdown, $line_number);
            $countdown -= 1;
        }

        $page_number = 2;
        $varMap2 = new ArrayVarMap(['page' => $page_number]);
        $result2 = $csp->get_reports_for_page($varMap2, $cspStorage);
        $this->assertSame([0,2], $cspStorage->getPagesRequested());


        $data = json_decode_safe($result2->getBody());
        $this->assertArrayHasKey("reports", $data);
        $reports = $data["reports"];

        $countdown = 199 - ($page_number * CSPViolationStorage::REPORTS_PER_PAGE);
        foreach ($reports as $report) {
            $this->assertArrayHasKey("line-number", $report);
            $line_number = $report["line-number"];

            $this->assertEquals($countdown, $line_number);
            $countdown -= 1;
        }
    }
}