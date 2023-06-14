<?php

namespace Bristolian\AppController;

class Debug
{
    public function debug_page(
        \Predis\Client $redisClient,
        \Redis $redis
    ) {
        $contents = "Redis testing";

        $written = $redisClient->setex(
            "John",
            3600,
            "Hello there John",
        );

        /** @var $written \Predis\Response\Status */

        if ($written->getPayload() === 'OK') {
            $contents .= "Yaay, was written.";
        }
        else {
            $contents .= "Boo was not written.";
        }

        $contents .= "Contents of John are: ". $redisClient->get("John") . "<br/>";

        return $contents;
    }

    public function debug_redis(\Predis\Client $redisClient, \Redis $redis)
    {
        $contents = "Lets debug redis";
        $contents .= "Contents of John are: ". $redisClient->get("John");

        return $contents;
    }

}