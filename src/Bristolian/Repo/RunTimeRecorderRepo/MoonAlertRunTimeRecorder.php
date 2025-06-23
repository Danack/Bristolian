<?php

namespace Bristolian\Repo\RunTimeRecorderRepo;

interface MoonAlertRunTimeRecorder
{
    const STATE_INITIAL = 'initial';
//    const STATE_RUNNING = 'running';
    const STATE_FINISHED = 'finished';

    public function getLastRunTime(): \DateTimeInterface|null;

    public function startRun(): string;

    public function setRunFinished(string $id): void;
}
