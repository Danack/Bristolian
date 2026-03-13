<?php

namespace Bristolian\CliController;

use Bristolian\Model\Types\BccTro;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Service\BccTroFetcher\BccTroFetcher;
use Bristolian\Service\CliOutput\CliOutput;
use Bristolian\Service\DailyProcessorSchedule\DailyProcessorSchedule;

/**
 * @param BccTro[] $tros
 * @return void
 */
function output_tro_list_to_output($tros)
{
    if (empty($tros)) {
        echo "No TROs found.\n";
        return;
    }

    echo "Found " . count($tros) . " TRO(s):\n\n";

    foreach ($tros as $tro) {
        echo "Title: " . $tro->title . "\n";
        echo "Reference: " . $tro->reference_code . "\n";

        if (!empty($tro->statement_of_reasons->title)) {
            echo "Statement of Reasons: " . $tro->statement_of_reasons->title . "\n";
            echo "  Link: " . $tro->statement_of_reasons->href . "\n";
        }

        if (!empty($tro->notice_of_proposal->title)) {
            echo "Notice of Proposal: " . $tro->notice_of_proposal->title . "\n";
            echo "  Link: " . $tro->notice_of_proposal->href . "\n";
        }

        if (!empty($tro->proposed_plan->title)) {
            echo "Proposed Plan: " . $tro->proposed_plan->title . "\n";
            echo "  Link: " . $tro->proposed_plan->href . "\n";
        }

        echo "---\n";
    }
}

class BccTroFetcherCliController
{
    public function fetchTros(
        BccTroFetcher $bccTroFetcher,
        BccTroRepo $bccTroRepo,
        CliOutput $cliOutput,
        string $output
    ): void {
        $cliOutput->write("Fetching TRO data from Bristol City Council...\n");

        try {
            $tros = $bccTroFetcher->fetchTros();
        }
        catch (\Exception $exception) {
            $cliOutput->write("Error fetching TRO data: " . $exception->getMessage() . "\n");
            $cliOutput->exit(1);
        }

        $bccTroRepo->saveData($tros);
    }

    public function daily_bcc_tro(
        ProcessorRunRecordRepo $processorRunRecordRepo,
        BccTroFetcher $bccTroFetcher,
        DailyProcessorSchedule $dailyProcessorSchedule,
        CliOutput $cliOutput,
        string $output
    ): void {
        // @codeCoverageIgnoreStart
        $cliOutput->write("Fetching TRO data from Bristol City Council...\n");
        $callable = function () use (
            $processorRunRecordRepo,
            $bccTroFetcher,
            $dailyProcessorSchedule,
            $cliOutput
        ) {
            $this->runInternal(
                $processorRunRecordRepo,
                $bccTroFetcher,
                $dailyProcessorSchedule,
                $cliOutput
            );
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenRuns = 30,
            $sleepTime = 20,
            $maxRunTime = 6000
        );
        // @codeCoverageIgnoreEnd
    }

    public function runInternal(
        ProcessorRunRecordRepo $processorRunRecordRepo,
        BccTroFetcher $bccTroFetcher,
        DailyProcessorSchedule $dailyProcessorSchedule,
        CliOutput $cliOutput,
    ): void {
        $cliOutput->write("I am the daily_bcc_tro processor\n");

        if ($dailyProcessorSchedule->isTimeToRunDailySystemInfo() !== true) {
            $cliOutput->write("Skipping, not currently time to process_daily_system_info\n");
            return;
        }

        $last_run_time = $processorRunRecordRepo->getLastRunDateTime(
            ProcessType::daily_bcc_tro
        );

        if ($last_run_time !== null) {
            if ($dailyProcessorSchedule->isOverXHoursAgo(21, $last_run_time) === false) {
                $cliOutput->write("Skipping, daily_bcc_tro was run within the last 21 hours.\n");
                return;
            }
        }

        $run_id = $processorRunRecordRepo->startRun(
            ProcessType::daily_bcc_tro
        );

        $cliOutput->write("Fetching TROs.\n");
        $bccTroFetcher->fetchTros();

        $processorRunRecordRepo->setRunFinished(
            $run_id,
            ""
        );

        $cliOutput->write("Fin.\n");
    }
}
