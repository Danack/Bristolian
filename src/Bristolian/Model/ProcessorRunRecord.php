<?php

namespace Bristolian\Model;

use Bristolian\FromArray;
use Bristolian\ToArray;

class ProcessorRunRecord
{
    use ToArray;
    use FromArray;

    public function __construct(
        public readonly int $id,
        public readonly \DateTimeInterface $start_time,
        public readonly \DateTimeInterface|null $end_time,
        public readonly string $status,
        public readonly string $debug_info,
        public readonly string $processor_type,
    ) {
    }
}
