<?php

namespace Bristolian\Model;

class RunTimeRecord
{
    public function __construct(
        public readonly string $id,
        public readonly \DateTimeInterface|null $end_time,
        public readonly \DateTimeInterface|null $start_time,
        public readonly string $status,
        public readonly string $task,
    ) {

    }
}