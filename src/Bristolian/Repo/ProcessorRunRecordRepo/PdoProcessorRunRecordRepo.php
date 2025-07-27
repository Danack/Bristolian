<?php

namespace Bristolian\Repo\ProcessorRunRecordRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Database\processor_run_record;
use Bristolian\Model\ProcessorRunRecord;

use Bristolian\Repo\ProcessorRepo\ProcessType;

class PdoProcessorRunRecordRepo implements ProcessorRunRecordRepo
{
    public function __construct(private PdoSimple $pdo)
    {
    }

    public function getLastRunDateTime(ProcessType $process_type): \DateTimeInterface|null
    {
        $query = processor_run_record::SELECT;
        $query .= "where processor_type = :processor_type";
        $query .= " order by created_at desc limit 1";

        $params = [
            ':processor_type' => $process_type->value
        ];

        $objectOrNull = $this->pdo->fetchOneAsObjectOrNullConstructor(
            $query,
            $params,
            ProcessorRunRecord::class
        );

        if ($objectOrNull === null) {
            return null;
        }

        return $objectOrNull->created_at;
    }

    public function markJustRun(ProcessType $process_type, string $debug_info): void
    {
        $params = [
            'debug_info' => $debug_info,
            'processor_type' => $process_type->value
        ];

        $this->pdo->insert(
            processor_run_record::INSERT,
            $params
        );
    }
}
