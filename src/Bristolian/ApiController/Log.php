<?php

declare(strict_types = 1);

namespace Bristolian\ApiController;

use Bristolian\PdoSimple\PdoSimple;


use Bristolian\Repo\ProcessorRepo\ProcessType;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Parameters\ProcessorRunRecordTypeParam;
use VarMap\VarMap;
use Bristolian\Response\Typed\GetLogProcessorRunRecordsResponse;

class Log
{
    public function get_processor_run_records(
        ProcessorRunRecordRepo $processorRunRecordRepo,
        PdoSimple $pdoSimple,
        VarMap $varMap,
    ): GetLogProcessorRunRecordsResponse|JsonResponse {
        $params = [];
        // TODO - protect with login?

        $task_type = null;

        $params = ProcessorRunRecordTypeParam::createFromVarMap($varMap);

        $task_type = $params->task_type;

        $db_data = $processorRunRecordRepo->getRunRecords($task_type);

//        [$error, $value] = \convertToValue($db_data);
//
//        if ($error !== null) {
//            $data = [
//                'status' => 'error',
//                'message' => $error,
//                'description' => $error,
//            ];
//            return new JsonResponse(
//                $data,
//                [],
//                500
//            );
//        }

        return new GetLogProcessorRunRecordsResponse($db_data);
    }
}
