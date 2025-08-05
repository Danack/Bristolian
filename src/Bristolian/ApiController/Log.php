<?php

namespace Bristolian\ApiController;

use Bristolian\PdoSimple\PdoSimple;

use \Bristolian\Database\processor_run_records;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\DataType\ProcessorRunRecordTypeParam;
use VarMap\VarMap;

class Log
{
    public function get_processor_run_records(
        ProcessorRunRecordRepo $processorRunRecordRepo,
        PdoSimple $pdoSimple,
        VarMap $varMap,
    ): JsonResponse {
        $params = [];
        // TODO - protect with login?

        $task_type = null;

        $params = ProcessorRunRecordTypeParam::createFromVarMap($varMap);

        if ($params->task_type !== null) {
            $task_type = ProcessType::from($params->task_type);
        }

        $db_data = $processorRunRecordRepo->getRunRecords($task_type);

        [$error, $value] = \convertToValue($db_data);

        if ($error !== null) {
            $data = [
                'status' => 'error',
                'message' => $error,
                'description' => $error,
            ];
            return new JsonResponse(
                $data,
                [],
                500
            );
        }

//        var_dump($value);

//        $data = ['run_records' => $db_data];

        $data = ['run_records' => $value];


        return new JsonResponse(['status' => 'ok', 'data' => $data]);
    }
}
