<?php

namespace Bristolian\Service\DailyProcessorSchedule;

class LocalDevBccTroExecutionCheck implements BccTroExecutionCheck
{
    private $already_run = false;

    public function shouldRun(\DateTimeInterface|null $last_run_time): bool
    {
        if ($last_run_time === null) {
            return true;
        }

        // First run always succeeds for local dev, as we're testing, probably.
        if ($this->already_run === false) {
            $already_run = true;
            return true;
        }

        $now = new \DateTimeImmutable();
        $one_hour_ago = $now->sub(new \DateInterval(sprintf('PT%dH', 1)));

        return $last_run_time < $one_hour_ago;
    }
}


