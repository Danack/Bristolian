<?php

namespace Bristolian\CliController;

use AurorasLive\SunCalc;
//use Bristolian\MoonAlert\MoonAlertRepo;
use Bristolian\Service\MoonAlertNotifier\MoonAlertNotifier;
use Bristolian\Repo\ProcessorRepo\ProcessorRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;

function getMoonInfo(): string
{

    $bristol_lat = 51.4545;
    $bristol_lng = 360 -2.5879;

    $date = new \DateTime();

    $suncalc = new SunCalc($date, $bristol_lat, $bristol_lng);

    $sun_timees = $suncalc->getSunTimes();

    $sunset_time = 'not_set';
    if (array_key_exists('sunset', $sun_timees)) {
        $sunset_time = $sun_timees['sunset'];
    }

    $moon_visible_fraction = 'not set';
    $moon_phase = 'not set';

    $ilumination = $suncalc->getMoonIllumination();

    if (array_key_exists('fraction', $ilumination)) {
        $moon_visible_fraction = $ilumination['fraction'];
    }
    if (array_key_exists('phase', $ilumination)) {
        $moon_phase = $ilumination['phase'];
    }

    $moon_rise_time = null;

    $moontimes = $suncalc->getMoonTimes(true);

    if (array_key_exists('moonrise', $moontimes)) {
        $moon_rise_time = $moontimes['moonrise'];
    }

    $moon_rise_text = "No moon rise?";
    if ($moon_rise_time) {
        $moon_position_at_rise = $suncalc->getMoonPosition($moon_rise_time);

        $moon_rise_text = <<< TEXT
azimuth: {$moon_position_at_rise->azimuth}
altitude: {$moon_position_at_rise->altitude}
dist: {$moon_position_at_rise->dist}
TEXT;
    }

    $text = <<< TEXT
visible fraction: $moon_visible_fraction
moon rise time: {$moon_rise_time->format("H:i:s")}
sunset time: {$sunset_time->format("H:i:s")}

$moon_rise_text

TEXT;

    return $text;
}

function isTimeToProcessMoonInfo(): bool
{

//        echo getMoonInfo();
    // Get the current datetime
    $currentDateTime = new \DateTime();

    // Get the current hour
    $currentHour = (int)$currentDateTime->format('H');

    // Check if it's past noon (12:00 PM) and before 7pm.
    if ($currentHour >= 12 && $currentHour <= 18) {
        return true;
    }
    return false;
}

class MoonInfo
{
    public function __construct(
        //        private MoonAlertRepo $moonAlertRepo,
        private MoonAlertNotifier $moonAlertNotifier,
        private ProcessorRepo $processorRepo
    ) {
    }


    public function info(): void
    {
        echo "Run internal.\n";

        if (isTimeToProcessMoonInfo() !== true) {
            echo "Not time to process moon info.\n";
            return;
        }

        if ($this->processorRepo->getProcessorEnabled(ProcessType::moon_alert) !== true) {
            echo "ProcessType::moon_alert is not enabled..\n";
            return;
        };

        $moon_info = getMoonInfo();

        echo $moon_info;
        echo "\n";

        $this->moonAlertNotifier->notifyRegisteredUsers($moon_info);
    }

    /**
     * This is a placeholder background task
     */
    public function run(): void
    {
        $callable = function () {
            $this->info();
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenRuns = 30,
            $sleepTime = 1,
            $maxRunTime = 3600
        );
    }
}
