<?php

declare(strict_types = 1);


namespace Bristolian\CSPViolation;

interface CSPViolationManager
{
    /** Empty all the reports */
    public function clearReports(): void;
}
