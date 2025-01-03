<?php

namespace BristolianTest\CSPViolation;


use BristolianTest\BaseTestCase;
use Bristolian\CSPViolation\RedisCSPViolationStorage;
use BristolianTest\Repo\TestPlaceholders;
use Bristolian\CSPViolation\CSPViolationStorage;

/**
 * @covers \Bristolian\CSPViolation\RedisCSPViolationStorage
 */
class RedisCSPViolationStorageTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks()
    {
        $cspViolationStorage = $this->injector->make(RedisCSPViolationStorage::class);

        $cspViolationStorage->clearReports();

        $this->assertSame(0, $cspViolationStorage->getCount());
        $this->assertEmpty($cspViolationStorage->getReports());

        $cspReport = $this->createContentPolicyViolationReport([]);

        $cspViolationStorage->report($cspReport);

        $this->assertSame(1, $cspViolationStorage->getCount());
        $reports = $cspViolationStorage->getReports();
        $this->assertCount(1, $reports);
        $this->assertEquals($cspReport, $reports[0]);

        $cspViolationStorage->clearReports();
        $this->assertSame(0, $cspViolationStorage->getCount());
        $this->assertEmpty($cspViolationStorage->getReports());

        for ($i = 0; $i < 100 ; $i += 1) {
            $cspReport = $this->createContentPolicyViolationReport(['line-number' => 100 + $i]);
            $cspViolationStorage->report($cspReport);
        }

        $page_number = 2;
        $reports = $cspViolationStorage->getReportsByPage($page_number);
        $this->assertCount(CSPViolationStorage::REPORTS_PER_PAGE, $reports);
        // reports are returned most recent first.
        $expected_line_number = 200 - (($page_number * CSPViolationStorage::REPORTS_PER_PAGE) + 1);
        $this->assertSame("$expected_line_number", $reports[0]->getLineNumber());
    }
}