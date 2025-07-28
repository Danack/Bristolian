<?php

namespace Bristolian\CliController;

use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;

use Bristolian\Repo\EmailQueue\EmailQueue;

function isOverXHoursAgo(int $hours, \DateTimeInterface $datetime): bool
{
    $now = new \DateTimeImmutable(); // current time

    $interval_format = sprintf('PT%dH', $hours);

    $threshold = $now->sub(new \DateInterval($interval_format)); // hours ago
    return $datetime < $threshold;
}


function isTimeToRunDailySystemInfo(): bool
{
    $now = new \DateTime(); // current time

    // Create DateTime objects for today at 12:00 and 15:00
    $start = (clone $now)->setTime(12, 0); // 12:00 PM
    $end = (clone $now)->setTime(15, 0);   // 3:00 PM

    if ($now >= $start && $now < $end) {
        // echo "The time is between noon and 3 PM.";
        return true;
    }

    // echo "The time is NOT between noon and 3 PM.";
    return false;
}


class SystemInfo
{
    public function __construct(
        private ProcessorRunRecordRepo $processorRunRecordRepo,
        private EmailQueue $emailQueue,
    ) {
    }



    public function process_daily_system_info(): void
    {
        $callable = function () {
            $this->runInternal();
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenRuns = 30,
            $sleepTime = 20,
            $maxRunTime = 6000
        );
    }

    public function runInternal(): void
    {
        echo "I am the daily system info.\n";

        // Check the time, if it is between noon and 3pm, exit
        if (isTimeToRunDailySystemInfo() !== true) {
            echo "Skipping, not currently time to process_daily_system_info";
            return;
        }

        // Get the last run time, if it is less than 21 hours then exit
        $last_run_time = $this->processorRunRecordRepo->getLastRunDateTime(
            ProcessType::daily_system_info
        );

        if ($last_run_time !== null) {
            if (isOverXHoursAgo(21, $last_run_time) === false) {
                echo "Skipping, process_daily_system_info was run within the last 21 hours.";
                return;
            }
        }

        // Generate the system info email
        echo "Email generated, queueing to send.\n";

        // Put it in the email queue
        $this->emailQueue->queueEmailToUsers(
            ['danack@basereality.com'],
            $subject = "Bristolian daily system info",
            $body = "Shamoan"
        );

        // Mark last run time.
        $this->processorRunRecordRepo->markJustRun(
            ProcessType::daily_system_info,
            "no debug info"
        );
    }
}
