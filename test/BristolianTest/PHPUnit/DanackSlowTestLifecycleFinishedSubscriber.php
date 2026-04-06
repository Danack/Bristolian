<?php

declare(strict_types=1);

namespace BristolianTest\PHPUnit;

use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

final class DanackSlowTestLifecycleFinishedSubscriber implements FinishedSubscriber
{
    public function __construct(
        private readonly DanackSlowTestReportCollector $collector,
    ) {
    }

    public function notify(Finished $event): void
    {
        $this->collector->onTestFinished($event);
    }
}
