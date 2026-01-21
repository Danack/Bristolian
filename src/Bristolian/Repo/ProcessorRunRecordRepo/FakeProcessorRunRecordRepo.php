<?php

declare(strict_types = 1);

namespace Bristolian\Repo\ProcessorRunRecordRepo;

use Bristolian\Model\Generated\ProcessorRunRecord;
use Bristolian\Repo\ProcessorRepo\ProcessType;

/**
 * Fake implementation of ProcessorRunRecordRepo for testing.
 */
class FakeProcessorRunRecordRepo implements ProcessorRunRecordRepo
{
    /**
     * @var ProcessorRunRecord[]
     */
    private array $runRecords = [];

    private int $nextId = 1;

    public function getLastRunDateTime(ProcessType $process_type): \DateTimeInterface|null
    {
        $records = $this->getRunRecords($process_type);

        if (empty($records)) {
            return null;
        }

        // Records are already sorted by id desc (newest first) from getRunRecords
        return $records[0]->start_time;
    }

    public function startRun(ProcessType $process_type): string
    {
        $now = new \DateTimeImmutable();

        $record = new ProcessorRunRecord(
            id: $this->nextId++,
            processor_type: $process_type->value,
            debug_info: '',
            start_time: $now,
            status: self::STATE_INITIAL,
            end_time: null,
        );

        $this->runRecords[] = $record;

        return (string)$record->id;
    }

    public function setRunFinished(string $id, string $debug_info): void
    {
        foreach ($this->runRecords as $index => $record) {
            if ((string)$record->id === $id) {
                $updatedRecord = new ProcessorRunRecord(
                    id: $record->id,
                    processor_type: $record->processor_type,
                    debug_info: $debug_info,
                    start_time: $record->start_time,
                    status: self::STATE_FINISHED,
                    end_time: new \DateTimeImmutable(),
                );

                $this->runRecords[$index] = $updatedRecord;
                return;
            }
        }
    }

    /**
     * @param ProcessType|null $processType
     * @return ProcessorRunRecord[]
     */
    public function getRunRecords(ProcessType|null $processType): array
    {
        $filtered = [];

        foreach ($this->runRecords as $record) {
            if ($processType === null || $record->processor_type === $processType->value) {
                $filtered[] = $record;
            }
        }

        // Sort by id desc (newest first), limit to 50
        usort($filtered, function (ProcessorRunRecord $a, ProcessorRunRecord $b) {
            return $b->id <=> $a->id;
        });

        return array_slice($filtered, 0, 50);
    }
}
