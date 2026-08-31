<?php

namespace Bristolian\Service\DailyProcessorSchedule;

interface BccTroExecutionCheck
{
    public function shouldRun(\DateTimeInterface|null $last_run_time): bool;
}