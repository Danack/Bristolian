<?php

namespace Bristolian\CliController;

use Bristolian\Model\Types\BccTro;
use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Service\BccTroFetcher\BccTroFetcher;
use Bristolian\Service\CliOutput\CliOutput;
use Bristolian\Service\DailyProcessorSchedule\DailyProcessorSchedule;
use Bristolian\Service\RoomMessageService\RoomMessageService;
use Bristolian\Service\DailyProcessorSchedule\BccTroExecutionCheck;
use Bristolian\Service\BccTroService\BccTroService;




class BccTroFetcherCliController
{
    public function continual_bcc_tro_process(
        BccTroExecutionCheck $bccTroExecutionCheck,
        ProcessorRunRecordRepo $processorRunRecordRepo,
        BccTroService $bccTroSerice,
        CliOutput $cliOutput
    ): void {
        // @codeCoverageIgnoreStart
        $cliOutput->write("Running continual_bcc_tro_process\n");

        $callable = function () use (
            $bccTroExecutionCheck,
            $processorRunRecordRepo,
            $bccTroSerice,
            $cliOutput
        ) {
            $this->single_bcc_tro_process(
                $bccTroExecutionCheck,
                $processorRunRecordRepo,
                $bccTroSerice,
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
        BccTroService $bccTroSerice,
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
            $bccTroSerice->do_the_needful();
            $processorRunRecordRepo->setRunFinished(
                $run_id,
                ""
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
