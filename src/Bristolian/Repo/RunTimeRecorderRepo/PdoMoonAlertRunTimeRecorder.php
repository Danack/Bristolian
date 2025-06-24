<?php

namespace Bristolian\Repo\RunTimeRecorderRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Database\run_time_recorder;
use Bristolian\App;
use Bristolian\Model\RunTimeRecord;

class PdoMoonAlertRunTimeRecorder implements MoonAlertRunTimeRecorder
{
    const TASK_NAME = 'run_moon_alert';

    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    public function getLastRunTime(): \DateTimeInterface|null
    {
        $sql = run_time_recorder::SELECT;
        $sql .= "where task = '" . self::TASK_NAME . "' order by id desc limit 1";

        $data = $this->pdoSimple->fetchAllAsData($sql, []);

        if (count($data) === 0) {
            return null;
        }

        return \DateTimeImmutable::createFromFormat(
            App::MYSQL_DATE_TIME_FORMAT,
            $data[0]["start_time"]
        );
    }

    public function startRun(): string
    {
        $sql = <<< SQL
insert into run_time_recorder (
    end_time,
    start_time,
    status,
    task
)
values (
    :end_time,
    :start_time,
    :status,
    :task
)
SQL;

        $params = [
            ':end_time' => null,
            ':start_time' => (new \DateTimeImmutable())->format(App::MYSQL_DATE_TIME_FORMAT),
            ':status' => self::STATE_INITIAL,
            ':task' => self::TASK_NAME
        ];

        $result = $this->pdoSimple->insert($sql, $params);

        return (string)$result;
    }

    public function setRunFinished(string $id): void
    {
        $sql = <<< SQL
update
  run_time_recorder 
set 
  end_time = :end_time,
  status = :status
where
  id = :id
limit 1
SQL;

        $params = [
            ':end_time' => (new \DateTimeImmutable())->format(App::MYSQL_DATE_TIME_FORMAT),
            ':status' => self::STATE_FINISHED,
            ':id' => $id
        ];
        $this->pdoSimple->execute($sql, $params);
    }


    /**
     * For testing only.
     * Don't run this in production, m'kay?
     */
    public function deleteAllRuns(): void
    {
        $sql = <<< SQL
delete from 
  run_time_recorder
where
  task = :task
SQL;

        $params = [
            ':task' => self::TASK_NAME,
        ];

        $this->pdoSimple->execute($sql, $params);
    }

    /**
     * @param string $id
     * @return array<string, string|int>
     * @throws \Bristolian\PdoSimple\PdoSimpleException
     */
    public function getRunState(string $id): array
    {
        $sql = run_time_recorder::SELECT;
        $sql .= "where id = :id";

        $params = [
            ':id' => $id,
        ];
        $data = $this->pdoSimple->fetchAllAsData($sql, $params);

        return $data[0];
    }
}
