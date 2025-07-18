<?php

namespace BristolianTest\PdoSimple;

use Bristolian\DataType\PropertyType\BasicString;
use Bristolian\DataType\PropertyType\SourceLinkPositionValue;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class PdoSimpleTestObjectConstructor
{
    public function __construct(
        public readonly int $id,
        public readonly string $test_string,
        public readonly int $test_int,
        public readonly \DateTimeInterface $created_at,
    ) {
    }
}
