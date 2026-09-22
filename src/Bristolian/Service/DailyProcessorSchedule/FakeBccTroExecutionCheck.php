<?php

declare(strict_types = 1);

namespace Bristolian\Service\DailyProcessorSchedule;

/**
 * Test double: control whether the BCC TRO processor should run.
 */
final class FakeBccTroExecutionCheck implements BccTroExecutionCheck
{
    public function __construct(
        private readonly bool $shouldRun = true
    ) {
    }

    public function shouldRun(\DateTimeInterface|null $last_run_time): bool
    {
        return $this->shouldRun;
    }
}
