<?php

namespace BristolianTest;

use Bristolian\DataType\BasicInteger;
use Bristolian\DataType\BasicString;
use Bristolian\FromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromArray;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class PdoSimpleTestObject implements DataType
{
    use CreateFromRequest;
    use CreateFromArray;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('test_string')]
        public readonly string $test_string,
        #[BasicInteger('test_int')]
        public readonly int $test_int,
    ) {
    }
}
