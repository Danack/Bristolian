<?php

namespace Bristolian\CliController;

use Bristolian\Repo\EmailQueue\EmailQueue;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Service\CliOutput\CliOutput;
use Bristolian\Service\DailyProcessorSchedule\DailyProcessorSchedule;

class SystemInfo
{
    public function __construct(
        private ProcessorRunRecordRepo $processorRunRecordRepo,
        private EmailQueue $emailQueue,
        private DailyProcessorSchedule $dailyProcessorSchedule,
        private CliOutput $cliOutput,
    ) {
    }

    public function process_daily_system_info(): void
    {
        // @codeCoverageIgnoreStart
        $callable = function () {
            $this->runInternal();
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenRuns = 30,
            $sleepTime = 20,
            $maxRunTime = 6000
        );
        // @codeCoverageIgnoreEnd
    }

    public function runInternal(): void
    {
        $this->cliOutput->write("I am the daily system info.\n");

        if ($this->dailyProcessorSchedule->isTimeToRunDailySystemInfo() !== true) {
            $this->cliOutput->write("Skipping, not currently time to process_daily_system_info\n");
            return;
        }

        $last_run_time = $this->processorRunRecordRepo->getLastRunDateTime(
            ProcessType::daily_system_info
        );

        if ($last_run_time !== null) {
            if ($this->dailyProcessorSchedule->isOverXHoursAgo(21, $last_run_time) === false) {
                $this->cliOutput->write("Skipping, process_daily_system_info was run within the last 21 hours.\n");
                return;
            }
        }

        $run_id = $this->processorRunRecordRepo->startRun(
            ProcessType::daily_system_info
        );

        $this->cliOutput->write("Email generated, queueing to send.\n");

        $this->emailQueue->queueEmailToUsers(
            ['danack@basereality.com'],
            $subject = "Daily system info",
            $body = generateSystemInfoEmailContent()
        );

        $this->processorRunRecordRepo->setRunFinished(
            $run_id,
            ""
        );

        $this->cliOutput->write("Fin.\n");
    }
}
