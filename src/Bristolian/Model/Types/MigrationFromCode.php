<?php

namespace Bristolian\Model\Types;

class MigrationFromCode
{
    public function __construct(
        public readonly int $id,
        public readonly string $description,
        /**
         * @var string[]
         */
        public readonly array $queries_to_run,
    ) {
    }
}
