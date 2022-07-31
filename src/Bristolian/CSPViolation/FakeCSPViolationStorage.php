<?php

declare(strict_types = 1);

namespace Bristolian\CSPViolation;

use Bristolian\Data\ContentPolicyViolationReport;

class FakeCSPViolationStorage implements CSPViolationStorage
{
    /** @var int  */
    private $clearCalls = 0;

    /** @var ContentPolicyViolationReport[]  */
    private array $reports = [];

    public function clearReports(): void
    {
        $this->clearCalls += 1;
    }

    /**
     * @return ContentPolicyViolationReport[]
     */
    public function getReports(): array
    {
        return $this->reports;
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
