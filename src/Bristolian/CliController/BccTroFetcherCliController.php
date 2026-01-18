<?php

namespace Bristolian\CliController;

use Bristolian\Model\Types\BccTro;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Service\BccTroFetcher\BccTroFetcher;

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
        string $output
    ): void {
        echo "Fetching TRO data from Bristol City Council...\n";

        var_dump($output);

        try {
            $tros = $bccTroFetcher->fetchTros();
        }
        catch (\Exception $e) {
            echo "Error fetching TRO data: " . $e->getMessage() . "\n";
            exit(1);
        }


        $bccTroRepo->saveData($tros);
//
//        var_dump($tros);

//        // 'CLI' or 'room'"
//        if (strcasecmp($output, 'CLI') === 0) {
//            output_tro_list_to_output($tros);
//        }
    }

    public function daily_bcc_tro(
        ProcessorRunRecordRepo $processorRunRecordRepo,
        BccTroFetcher $bccTroFetcher,
        string $output
    ): void {
        echo "Fetching TRO data from Bristol City Council...\n";

        $callable = function () use ($processorRunRecordRepo, $bccTroFetcher) {
            $this->runInternal(
                $processorRunRecordRepo,
                $bccTroFetcher
            );
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenRuns = 30,
            $sleepTime = 20,
            $maxRunTime = 6000
        );
    }

    public function runInternal(
        ProcessorRunRecordRepo $processorRunRecordRepo,
        BccTroFetcher $bccTroFetcher
    ): void {


        echo "I am the daily_bcc_tro processor\n";

        // Check the time, if it is between noon and 3pm, exit
        if (isTimeToRunDailySystemInfo() !== true) {
            echo "Skipping, not currently time to process_daily_system_info\n";
            return;
        }

        // Get the last run time, if it is less than 21 hours then exit
        $last_run_time = $processorRunRecordRepo->getLastRunDateTime(
            ProcessType::daily_bcc_tro
        );

        if ($last_run_time !== null) {
            if (isOverXHoursAgo(21, $last_run_time) === false) {
                echo "Skipping, daily_bcc_tro was run within the last 21 hours.\n";
                return;
            }
        }

        $run_id = $processorRunRecordRepo->startRun(
            ProcessType::daily_bcc_tro
        );


        // Generate the system info email

        // Fetch the TROs
        echo "Fetching TROs.\n";
        $tros = $bccTroFetcher->fetchTros();


//        // Put it in the email queue
//        $this->emailQueue->queueEmailToUsers(
//            ['danack@basereality.com'],
//            $subject = "Daily system info",
//            $body = generateSystemInfoEmailContent()
//        );

        // Mark last run time.
        $processorRunRecordRepo->setRunFinished(
            $run_id,
            ""
        );

        echo "Fin.\n";
    }
}
