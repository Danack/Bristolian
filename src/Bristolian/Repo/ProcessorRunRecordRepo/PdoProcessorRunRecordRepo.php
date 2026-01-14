<?php

namespace Bristolian\Repo\ProcessorRunRecordRepo;

use Bristolian\Database\processor_run_record;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\ProcessorRepo\ProcessType;
//use ProcessorRunRecord;

use Bristolian\Model\Generated\ProcessorRunRecord;

class PdoProcessorRunRecordRepo implements ProcessorRunRecordRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    public function getLastRunDateTime(ProcessType $process_type): \DateTimeInterface|null
    {
        $query = processor_run_record::SELECT;
        $query .= "where processor_type = :processor_type"; // TODO - normalise names
        $query .= " order by start_time desc limit 1";

        $params = [
            ':processor_type' => $process_type->value
        ];

        $objectOrNull = $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $query,
            $params,
            ProcessorRunRecord::class
        );

        if ($objectOrNull === null) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $objectOrNull->start_time;
    }

    public function startRun(ProcessType $process_type): string
    {
        $sql = processor_run_record::INSERT;

        $params = [
            ':end_time' => null,
            ':debug_info' => "",
            ':status' => self::STATE_INITIAL,
            ':processor_type' => $process_type->value
        ];

        $result = $this->pdoSimple->insert($sql, $params);

        return (string)$result;
    }

    public function setRunFinished(string $id, string $debug_info): void
    {

        $sql = <<< SQL
update
  processor_run_record
set 
  end_time = NOW(),
  status = :status
where
  id = :id
limit 1
SQL;

        $params = [
            ':status' => self::STATE_FINISHED,
            ':id' => $id
        ];
        $this->pdoSimple->execute($sql, $params);
    }

    /**
     * @param ProcessType|null $processType
     * @return \ProcessorRunRecord[]
     */
    public function getRunRecords(ProcessType|null $processType): array
    {
        $params = [];

        $sql = processor_run_record::SELECT;

        if ($processType !== null) {
            $sql .= " where processor_type = :processor_type";
            $params[':processor_type'] = $processType->value;
        }

        $sql .= " order by id desc limit 50";

        $db_data =  $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            ProcessorRunRecord::class
        );

        return $db_data;
    }
}
