<?php

namespace Bristolian\Repo\ProcessorRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Database\processor;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Model\ProcessorState;

class PdoProcessorRepo implements ProcessorRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    public function getProcessorsStates(): array
    {
        $sql = processor::SELECT;

        $processor_states = $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            [],
            ProcessorState::class
        );

        $keyed_states = [];
        foreach ($processor_states as $processor_state) {
            $keyed_states[$processor_state->type] = $processor_state;
        }


        return $keyed_states;
    }

    public function setProcessorEnabled(ProcessType $processor, bool $enabled): void
    {
        $enabled_int = (int)$enabled;

        $sql = "insert into processor (
    enabled,
    type
)
values (
    :enabled,
    :type
)";
        $sql .= " ON DUPLICATE KEY UPDATE enabled = :enabled_again";

        $params = [
            ':type' => $processor->value,
            ':enabled' => $enabled_int,
            ':enabled_again' => $enabled_int,
        ];

        $rows_affected = $this->pdoSimple->execute($sql, $params);

        \error_log("rows_affected = $rows_affected");
    }

    public function getProcessorEnabled(ProcessType $processor): bool
    {
        $sql = "select enabled from processor where type = :type";
        $result = $this->pdoSimple->fetchOneAsDataOrNull($sql, [':type' => $processor->value]);

        if ($result === null) {
            return false;
        }

        return (bool)$result['enabled'];
    }
}
