<?php

namespace Bristolian\ApiController;

use Bristolian\PdoSimple\PdoSimple;

use \Bristolian\Database\processor_run_records;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;

class Log
{
    public function get_processor_run_records(
        ProcessorRunRecordRepo $processorRunRecordRepo,
        PdoSimple $pdoSimple
    ): JsonResponse {
        $params = [];
        // TODO - protect with login?

        $task_type = null;
        $db_data = $processorRunRecordRepo->getRunRecords($task_type);
        $data = ['run_records' => $db_data];

        return new JsonResponse(['status' => 'ok', 'data' => $data]);
    }
}
