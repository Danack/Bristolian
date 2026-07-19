<?php

namespace Bristolian\Repo\ProcessorRunRecordRepo;

//bristolian.processor_run_record

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\processor_run_record;
use Bristolian\Repo\ProcessorRepo\ProcessType;

interface ProcessorRunRecordRepo
{
    const STATE_INITIAL = 'initial';
    const STATE_FINISHED = 'finished';

    #[ReadsTable(processor_run_record::class)]
    public function getLastRunDateTime(ProcessType $process_type): \DateTimeInterface|null;

    #[WritesTable(processor_run_record::class)]
    public function startRun(ProcessType $process_type): string;

    #[WritesTable(processor_run_record::class)]
    public function setRunFinished(string $id, string $debug_info): void;

    /**
     * @param ProcessType|null $processType
     * @return \Bristolian\Model\Generated\ProcessorRunRecord[]
     */
    #[ReadsTable(processor_run_record::class)]
    public function getRunRecords(ProcessType|null $processType): array;
}
