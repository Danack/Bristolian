<?php

namespace BristolianTest\CSPViolation;

use BristolianTest\BaseTestCase;
use Bristolian\CSPViolation\FakeCSPViolationStorage;
use BristolianTest\Repo\TestPlaceholders;
use Bristolian\CSPViolation\CSPViolationStorage;

/**
 * @covers \Bristolian\CSPViolation\FakeCSPViolationStorage
 */
class FakeCSPViolationStorageTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks()
    {
        $cspViolationStorage = new FakeCSPViolationStorage();

        $this->assertSame(0, $cspViolationStorage->getCount());
        $this->assertEmpty($cspViolationStorage->getReports());
        $this->assertSame(0, $cspViolationStorage->getClearCalls());

        $cspReport = $this->createContentPolicyViolationReport([]);

        $cspViolationStorage->report($cspReport);

        $this->assertSame(1, $cspViolationStorage->getCount());
        $reports = $cspViolationStorage->getReports();
        $this->assertCount(1, $reports);
        $this->assertSame($cspReport, $reports[0]);

        $cspViolationStorage->clearReports();
        $this->assertSame(0, $cspViolationStorage->getCount());
        $this->assertEmpty($cspViolationStorage->getReports());
        $this->assertSame(1, $cspViolationStorage->getClearCalls());

        for ($i = 0; $i < 100; $i += 1) {
            $cspReport = $this->createContentPolicyViolationReport(['line-number' => 100 + $i]);
            $cspViolationStorage->report($cspReport);
        }

        $page_number = 2;
        $reports = $cspViolationStorage->getReportsByPage($page_number);
        $this->assertCount(CSPViolationStorage::REPORTS_PER_PAGE, $reports);
        $expected_line_number = 200 - (($page_number * CSPViolationStorage::REPORTS_PER_PAGE) + 1);
        $this->assertSame("$expected_line_number", $reports[0]->getLineNumber());

        $this->assertSame([2], $cspViolationStorage->getPagesRequested());
    }
}
