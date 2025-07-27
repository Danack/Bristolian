<?php

namespace Bristolian\Model;

use Bristolian\FromArray;

class ProcessorRunRecord
{
    use FromArray;

    public function __construct(
        public readonly int $id,
        public readonly string $debug_info,
        public readonly string $processor_type,
        public readonly \DateTimeInterface $created_at
    ) {
    }
}
