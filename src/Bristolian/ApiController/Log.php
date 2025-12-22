<?php

namespace Bristolian\ApiController;

use Bristolian\PdoSimple\PdoSimple;


use Bristolian\Repo\ProcessorRepo\ProcessType;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Parameters\ProcessorRunRecordTypeParam;
use VarMap\VarMap;
use Bristolian\Response\Typed\GetLogProcessor_run_recordsResponse;

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

        return new GetLogProcessor_run_recordsResponse($db_data);
    }
}
