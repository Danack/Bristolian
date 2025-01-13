<?php

namespace Bristolian\Service\MoonAlertNotifier;

use Bristolian\MoonAlert\MoonAlertRepo;
use Bristolian\Repo\EmailQueue\EmailQueue;
use Bristolian\Repo\RunTimeRecorderRepo\MoonAlertRunTimeRecorder;

class StandardMoonAlertNotifier implements MoonAlertNotifier
{
    public function __construct(
        private EmailQueue $emailQueue,
        private MoonAlertRepo $moonAlertRepo,
        private MoonAlertRunTimeRecorder $runtimeRecorder
    ) {
    }

    public function notifyRegisteredUsers(string $mooninfo): void
    {
        // Get the last run time
        $last_run_time = $this->runtimeRecorder->getLastRunTime();

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
        $run_id = $this->runtimeRecorder->startRun();
        $users = $this->moonAlertRepo->getUsersForMoonAlert();

        $this->emailQueue->queueEmailToUsers(
            $users,
            "This is a moon alert email",
            $mooninfo
        );

        echo "setting run finished.\n";
        $this->runtimeRecorder->setRunFinished($run_id);

    }

}