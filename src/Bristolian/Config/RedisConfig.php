<?php

namespace Bristolian\Config;

class RedisConfig
{
    public function __construct(
        public readonly string $host,
        public readonly string $password,
        public readonly int $port)
    {
    }
}