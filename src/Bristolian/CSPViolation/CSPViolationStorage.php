<?php

declare(strict_types = 1);

namespace Bristolian\CSPViolation;

use Bristolian\Data\ContentPolicyViolationReport;

interface CSPViolationStorage extends CSPViolationReporter
{
    const REPORTS_PER_PAGE = 20;

    /**
     * @return ContentPolicyViolationReport[]
     */
    public function getReports();

    public function getCount() : int;

    public function clearReports(): void;

    /**
     * @return ContentPolicyViolationReport[]
     */
    public function getReportsByPage(int $page);
}
