<?php

namespace Bristolian\Repo\ProcessorRunRecordRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Database\processor_run_records;
use Bristolian\Model\ProcessorRunRecord;
use Bristolian\Repo\ProcessorRepo\ProcessType;

class PdoProcessorRunRecordRepo implements ProcessorRunRecordRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    public function getLastRunDateTime(ProcessType $process_type): \DateTimeInterface|null
    {
        $query = processor_run_records::SELECT;
        $query .= "where task = :processor_type"; // TODO - normalise names
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
            return null;
        }

        return $objectOrNull->start_time;
    }

    public function markJustRun(ProcessType $process_type, string $debug_info): void
    {
        $params = [
            'debug_info' => $debug_info,
            'task' => $process_type->value
        ];

        $this->pdoSimple->insert(
            processor_run_records::INSERT,
            $params
        );
    }

    public function startRun(ProcessType $process_type): string
    {
                $sql = <<< SQL
insert into processor_run_records (
    end_time,
    status,
    task
)
values (
    :end_time,
    :status,
    :task
)
SQL;

        $params = [
            ':end_time' => null,
//            ':start_time' => (new \DateTimeImmutable())->format(App::MYSQL_DATE_TIME_FORMAT),
            ':status' => self::STATE_INITIAL,
            ':task' => $process_type->value
        ];

        $result = $this->pdoSimple->insert($sql, $params);

        return (string)$result;
    }

    public function setRunFinished(string $id): void
    {

        $sql = <<< SQL
update
  processor_run_records 
set 
  end_time = NOW(),
  status = :status
where
  id = :id
limit 1
SQL;

        $params = [
//            ':end_time' => (new \DateTimeImmutable())->format(App::MYSQL_DATE_TIME_FORMAT),
            ':status' => self::STATE_FINISHED,
            ':id' => $id
        ];
        $this->pdoSimple->execute($sql, $params);
    }


    /**
     * @param ProcessType|null $task_type
     * @return \Bristolian\Model\ProcessorRunRecord[]
     */
    public function getRunRecords(ProcessType|null $task_type): array
    {
        $params = [];

        $sql = processor_run_records::SELECT;

        if ($task_type !== null) {
            $params[':task'] = $task_type->value;
        }

        $db_data =  $this->pdoSimple->fetchAllAsObject(
            $sql,
            $params,
            ProcessorRunRecord::class
        );

        return $db_data;
    }
}
