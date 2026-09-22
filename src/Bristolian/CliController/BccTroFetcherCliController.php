<?php

namespace Bristolian\CliController;

use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Service\BccTroFetcher\BccTroFetcher;
use Bristolian\Service\CliOutput\CliOutput;
use Bristolian\Service\DailyProcessorSchedule\BccTroExecutionCheck;

class BccTroFetcherCliController
{
    public function continual_bcc_tro_process(
        BccTroExecutionCheck $bccTroExecutionCheck,
        ProcessorRunRecordRepo $processorRunRecordRepo,
        BccTroFetcher $bccTroFetcher,
        BccTroRepo $bccTroRepo,
        CliOutput $cliOutput
    ): void {
        // @codeCoverageIgnoreStart
        $cliOutput->write("Running continual_bcc_tro_process\n");

        $callable = function () use (
            $bccTroExecutionCheck,
            $processorRunRecordRepo,
            $bccTroFetcher,
            $bccTroRepo,
            $cliOutput
        ) {
            $this->single_bcc_tro_process(
                $bccTroExecutionCheck,
                $processorRunRecordRepo,
                $bccTroFetcher,
                $bccTroRepo,
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

    public function single_bcc_tro_process(
        BccTroExecutionCheck $bccTroExecutionCheck,
        ProcessorRunRecordRepo $processorRunRecordRepo,
        BccTroFetcher $bccTroFetcher,
        BccTroRepo $bccTroRepo,
        CliOutput $cliOutput,
    ): void {
        $cliOutput->write("I am the daily_bcc_tro processor\n");

        $last_run_time = $processorRunRecordRepo->getLastRunDateTime(
            ProcessType::daily_bcc_tro
        );

        if ($bccTroExecutionCheck->shouldRun($last_run_time) !== true) {
            $cliOutput->write("Skipping, BccTroExecutionCheck failed\n");
            return;
        }

        $run_id = $processorRunRecordRepo->startRun(
            ProcessType::daily_bcc_tro
        );

        try {
            $page_html = $bccTroFetcher->fetchPage();

            $save_id_or_null = $bccTroRepo->saveDataIfNew($page_html);

            $message = "BccTro save_id_or_null is " . var_export($save_id_or_null, true) . "\n";
            $cliOutput->write($message);
            $processorRunRecordRepo->setRunFinished(
                $run_id,
                $message
            );
        }
        catch (\Exception $exception) {
            $processorRunRecordRepo->setRunFinished(
                $run_id,
                "Error running BccTroFetcherCliController: " . $exception->getMessage()
            );

            $cliOutput->write("Error running BccTroFetcherCliController: " . $exception->getMessage());
        }

        $cliOutput->write("Fin.\n");
    }
}
