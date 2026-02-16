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

    public function testWorks(): void
    {
        $csp = new Csp();
        $varMap = new ArrayVarMap([]);
        $cspStorage = new FakeCSPViolationStorage();

        for ($i = 0; $i < 100; $i++) {
            $cspReport = $this->createContentPolicyViolationReport(['line-number' => (string)(100 + $i)]);
            $cspStorage->report($cspReport);
        }

        $result = $csp->get_reports_for_page($varMap, $cspStorage);

        $this->assertSame([0], $cspStorage->getPagesRequested());

        $data = json_decode_safe($result->getBody());
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('reports', $data['data']);
        $this->assertArrayHasKey('count', $data['data']);
        $this->assertSame(100, $data['data']['count']);
        $reports = $data['data']['reports'];
        $this->assertCount(CSPViolationStorage::REPORTS_PER_PAGE, $reports);

        $countdown = 199;
        foreach ($reports as $report) {
            $this->assertArrayHasKey("line-number", $report);
            $line_number = $report["line-number"];

            $this->assertEquals($countdown, $line_number);
            $countdown -= 1;
        }

        $page_number = 2;
        $varMap2 = new ArrayVarMap(['page' => (string)$page_number]);
        $result2 = $csp->get_reports_for_page($varMap2, $cspStorage);
        $this->assertSame([0, 2], $cspStorage->getPagesRequested());

        $data = json_decode_safe($result2->getBody());
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('reports', $data['data']);
        $reports = $data['data']['reports'];

        $countdown = 199 - ($page_number * CSPViolationStorage::REPORTS_PER_PAGE);
        foreach ($reports as $report) {
            $this->assertArrayHasKey("line-number", $report);
            $line_number = $report["line-number"];

            $this->assertEquals($countdown, $line_number);
            $countdown -= 1;
        }
    }
}
