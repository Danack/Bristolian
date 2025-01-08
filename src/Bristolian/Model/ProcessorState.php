<?php

namespace Bristolian\Model;

use Bristolian\FromArray;

class ProcessorState
{
    use FromArray;

    public function __construct(
        public readonly string $id,
        public readonly bool $enabled,
        public readonly string $type,
        public readonly string  $updated_at
    ) {
    }
}
