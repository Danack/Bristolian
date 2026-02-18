<?php

namespace Bristolian\Repo\ProcessorRunRecordRepo;

use Bristolian\Database\processor_run_record;
use Bristolian\Model\Generated\ProcessorRunRecord;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\ProcessorRepo\ProcessType;

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

    private const DEBUG_INFO_MAX_BYTES = 1024;

    public function setRunFinished(string $id, string $debug_info): void
    {
        $truncated = mb_strcut($debug_info, 0, self::DEBUG_INFO_MAX_BYTES, 'UTF-8');

        $sql = <<< SQL
update
  processor_run_record
set 
  end_time = NOW(),
  debug_info = :debug_info,
  status = :status
where
  id = :id
limit 1
SQL;

        $params = [
            ':debug_info' => $truncated,
            ':status' => self::STATE_FINISHED,
            ':id' => $id
        ];
        $this->pdoSimple->execute($sql, $params);
    }

    /**
     * @param ProcessType|null $processType
     * @return \Bristolian\Model\Generated\ProcessorRunRecord[]
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
