<?php

namespace Bristolian\ApiController;

use Bristolian\PdoSimple\PdoSimple;

use \Bristolian\Database\processor_run_records;
use SlimDispatcher\Response\JsonResponse;

class Log
{
    function get_processor_run_records(PdoSimple $pdoSimple)
    {

        $params = [];

        // TODO - protect with login?



//        $params = [
//            ':user_id' => $user_id,
//            ':meme_tag_id' => $meme_tag_id
//        ];
        $sql = processor_run_records::SELECT;

        $db_data =  $pdoSimple->fetchAllAsData($sql, $params);

        $data = ['run_records' => $db_data];

        return new JsonResponse(['status' => 'ok', 'data' => $data]);
    }
}