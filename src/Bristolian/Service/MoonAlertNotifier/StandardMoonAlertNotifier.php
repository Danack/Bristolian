<?php

namespace Bristolian\Service\MoonAlertNotifier;

use Bristolian\MoonAlert\MoonAlertRepo;
use Bristolian\Repo\EmailQueue\EmailQueue;
//use Bristolian\Repo\RunTimeRecorderRepo\MoonAlertRunTimeRecorder;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;

class StandardMoonAlertNotifier implements MoonAlertNotifier
{
    public function __construct(
        private EmailQueue $emailQueue,
        private MoonAlertRepo $moonAlertRepo,
        private ProcessorRunRecordRepo $processorRunRecordRepo
    ) {
    }

    public function notifyRegisteredUsers(string $mooninfo): void
    {
        // Get the last run time
        $last_run_time = $this->processorRunRecordRepo->getLastRunDateTime(
            ProcessType::moon_alert
        );

        if ($last_run_time === null) {
            echo "Notifying users. job hasn't been previously run.\n";
        }
        else {
            // Get the current date and time
            $currentDateTime = new \DateTime();

            // Calculate the difference in hours
            $interval = $currentDateTime->diff($last_run_time);
            $hoursDifference = ($interval->days * 24) + $interval->h;

            // If it less than 12 hours ago
            if ($hoursDifference < 12) {
                echo "Not notifying users, less than 12 hours ago.\n";
                return;
            }
            echo "Notifying users. hours difference is $hoursDifference\n";
        }

        echo "setting run started.\n";
        $run_id = $this->processorRunRecordRepo->startRun(ProcessType::moon_alert);

        $users = $this->moonAlertRepo->getUsersForMoonAlert();

        $this->emailQueue->queueEmailToUsers(
            $users,
            "[Bristolian][Moon]This is a moon alert email",
            $mooninfo
        );

        echo "setting run finished.\n";
        $this->processorRunRecordRepo->setRunFinished($run_id);
        echo "Fin.\n";
    }
}
