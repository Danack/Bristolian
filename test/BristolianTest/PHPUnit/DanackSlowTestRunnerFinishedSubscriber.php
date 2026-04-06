<?php

declare(strict_types=1);

namespace BristolianTest\PHPUnit;

use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;

final class DanackSlowTestRunnerFinishedSubscriber implements FinishedSubscriber
{
    public function __construct(
        private readonly DanackSlowTestReportCollector $collector,
    ) {
    }

    public function notify(Finished $event): void
    {
        $this->collector->writeReportAfterTestRun($event);
    }
}
