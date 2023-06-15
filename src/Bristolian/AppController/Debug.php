<?php

namespace Bristolian\AppController;

class Debug
{
    public function debug_page(
        \Predis\Client $redisClient,
        \Redis $redis
    ): string {
        $contents = "Redis testing";


        $written = $redisClient->setex(
            "John",
            3600,
            "Hello there John",
        );

        /** @var \Predis\Response\Status $written */
        if ($written->getPayload() === 'OK') {
            $contents .= "Yaay, was written.";
        }
        else {
            $contents .= "Boo was not written.";
        }

        $contents .= "Contents of John are: ". $redisClient->get("John") . "<br/>";

        return $contents;
    }

    public function debug_redis(\Predis\Client $redisClient, \Redis $redis): string
    {
        $contents = "Lets debug redis";
        $contents .= "Contents of John are: ". $redisClient->get("John");

        return $contents;
    }

}