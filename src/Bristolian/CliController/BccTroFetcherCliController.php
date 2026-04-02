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

/**
 * @param BccTro[] $tros
 * @return string
 */
function output_tro_list_to_output($tros): string
{
    if (empty($tros)) {
        return "No TROs found.\n";
    }

    $output = "Found " . count($tros) . " TRO(s):\n\n";

    foreach ($tros as $tro) {
        $output .= "Title: " . $tro->title . "\n";
        $output .= "Reference: " . $tro->reference_code . "\n";

        if (!empty($tro->statement_of_reasons->title)) {
            $output .= "Statement of Reasons: " . $tro->statement_of_reasons->title . "\n";
            $output .= "  Link: " . $tro->statement_of_reasons->href . "\n";
        }

        if (!empty($tro->notice_of_proposal->title)) {
            $output .= "Notice of Proposal: " . $tro->notice_of_proposal->title . "\n";
            $output .= "  Link: " . $tro->notice_of_proposal->href . "\n";
        }

        if (!empty($tro->proposed_plan->title)) {
            $output .= "Proposed Plan: " . $tro->proposed_plan->title . "\n";
            $output .= "  Link: " . $tro->proposed_plan->href . "\n";
        }

        $output .= "---\n";
    }

    return $output;
}

class BccTroFetcherCliController
{
    public function fetchTros(
        BccTroFetcher $bccTroFetcher,
        BccTroRepo $bccTroRepo,
        RoomRepo $roomRepo,
        RoomMessageService $roomMessageService,
        CliOutput $cliOutput
    ): void {
        $cliOutput->write("Fetching TRO data from Bristol City Council...\n");
        $tros = $bccTroFetcher->fetchTros();

        $tro_info = "There were no TROs found.";

        if (count($tros) !== 0) {
            $id = $bccTroRepo->saveData($tros);
            $tro_info = "There were " . count($tros) . " TROs found. The saved data has ID " . $id;
            $tro_info .= output_tro_list_to_output($tros);
        }

        $transport_room_name = "Transport";
        $rooms = $roomRepo->getRoomByName($transport_room_name);

        if (count($rooms) === 0) {
            $cliOutput->write("Failed to find '$transport_room_name'.");
            return;
        }

        $params = [
            'text' => "BCC TRO has been updated: " . $tro_info,
            'room_id' => ($rooms[0])->id,
        ];

        $messageParams = ChatMessageParam::createFromArray($params);
        $chat_message = $roomMessageService->sendRoomMessage($messageParams);
    }

    public function daily_bcc_tro(
        ProcessorRunRecordRepo $processorRunRecordRepo,
        BccTroFetcher $bccTroFetcher,
        BccTroRepo $bccTroRepo,
        RoomRepo $roomRepo,
        RoomMessageService $roomMessageService,
        DailyProcessorSchedule $dailyProcessorSchedule,
        CliOutput $cliOutput
    ): void {
        // @codeCoverageIgnoreStart
        $cliOutput->write("Fetching TRO data from Bristol City Council...\n");
        $callable = function () use (
            $processorRunRecordRepo,
            $bccTroFetcher,
            $bccTroRepo,
            $dailyProcessorSchedule,
            $roomRepo,
            $roomMessageService,
            $cliOutput
        ) {
            $this->runInternal(
                $processorRunRecordRepo,
                $bccTroFetcher,
                $bccTroRepo,
                $dailyProcessorSchedule,
                $roomRepo,
                $roomMessageService,
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
        BccTroRepo $bccTroRepo,
        DailyProcessorSchedule $dailyProcessorSchedule,
        RoomRepo $roomRepo,
        RoomMessageService $roomMessageService,
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

        try {
            $this->fetchTros(
                $bccTroFetcher,
                $bccTroRepo,
                $roomRepo,
                $roomMessageService,
                $cliOutput
            );

            $processorRunRecordRepo->setRunFinished(
                $run_id,
                ""
            );
        }
        catch (\Exception $exception) {
            /*$cliOutput->write("Error fetching TRO data: " . $exception->getMessage() . "\n");
            $cliOutput->exit(1);*/

            $processorRunRecordRepo->setRunFinished(
                $run_id,
                "Error fetching TRO data: " . $exception->getMessage()
            );
        }

        $cliOutput->write("Fin.\n");
    }
}
