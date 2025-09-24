<?php

namespace Bristolian\Repo\ProcessorRunRecordRepo;

//bristolian.processor_run_record

//use Bristolian\Database\processor_run_records;
use Bristolian\Repo\ProcessorRepo\ProcessType;

interface ProcessorRunRecordRepo
{
    const STATE_INITIAL = 'initial';
//    const STATE_RUNNING = 'running';
    const STATE_FINISHED = 'finished';
    // const STATE_ABORTED = 'aborted'; // not used yet.

    public function getLastRunDateTime(ProcessType $process_type): \DateTimeInterface|null;

    public function startRun(ProcessType $process_type): string;

    public function setRunFinished(string $id, string $debug_info): void;

    /**
     * @param ProcessType|null $processType
     * @return \Bristolian\Model\ProcessorRunRecord[]
     */
    public function getRunRecords(ProcessType|null $processType): array;
}
