<?php

namespace Bristolian\Model;

use Bristolian\FromArray;

class ProcessorRunRecord
{
    use FromArray;

    public function __construct(
        public readonly int $id,
        public readonly \DateTimeInterface $start_time,
        public readonly \DateTimeInterface $end_time,
        public readonly string $status,
        public readonly string $task,
    ) {
    }
}
