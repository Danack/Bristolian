<?php

declare(strict_types = 1);

namespace Bristolian\CSPViolation;

use Bristolian\CSPViolation\CSPViolationReporter;
use Bristolian\Data\ContentPolicyViolationReport;

interface CSPViolationStorage extends CSPViolationReporter
{
    /** Empty all the reports */
    public function clearReports(): void;

    /**
     * @return ContentPolicyViolationReport[]
     */
    public function getReports();

    public function getCount() : int;
}
