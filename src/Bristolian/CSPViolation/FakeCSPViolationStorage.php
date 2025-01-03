<?php

declare(strict_types = 1);

namespace Bristolian\CSPViolation;

use Bristolian\Data\ContentPolicyViolationReport;

class FakeCSPViolationStorage implements CSPViolationStorage
{
    private int $clearCalls = 0;

    /** @var ContentPolicyViolationReport[]  */
    private array $reports = [];

    public function clearReports(): void
    {
        $this->clearCalls += 1;
        $this->reports = [];
    }

    /**
     * @return ContentPolicyViolationReport[]
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    /**
     * @return ContentPolicyViolationReport[]
     */
    public function getReportsByPage(int $page)
    {
        $offset = $page * self::REPORTS_PER_PAGE;

        $reports_reversed = array_reverse($this->reports);
        return array_slice($reports_reversed, $offset, self::REPORTS_PER_PAGE);
    }

    public function report(ContentPolicyViolationReport $cpvr): void
    {
        $this->reports[] = $cpvr;
    }


    public function getCount(): int
    {
        return count($this->reports);
    }

    /**
     * @return int
     */
    public function getClearCalls(): int
    {
        return $this->clearCalls;
    }
}
