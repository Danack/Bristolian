<?php

declare(strict_types=1);

namespace BristolianTest\PHPUnit;

use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;

final class DanackSlowTestPreparedSubscriber implements PreparedSubscriber
{
    public function __construct(
        private readonly DanackSlowTestReportCollector $collector,
    ) {
    }

    public function notify(Prepared $event): void
    {
        $this->collector->onTestPrepared($event);
    }
}
