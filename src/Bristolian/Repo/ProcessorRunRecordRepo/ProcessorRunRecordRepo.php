<?php

namespace Bristolian\Repo\ProcessorRunRecordRepo;

//bristolian.processor_run_record

use Bristolian\Repo\ProcessorRepo\ProcessType;

interface ProcessorRunRecordRepo
{
    public function getLastRunDateTime(ProcessType $process_type): \DateTimeInterface|null;

    public function markJustRun(ProcessType $process_type, string $debug_info): void;
}
