<?php

namespace BristolianTest\PdoSimple;

use Bristolian\DataType\BasicString;
use Bristolian\DataType\SourceLinkPositionValue;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
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
        #[SourceLinkPositionValue('test_int')]
        public readonly int $test_int,
    ) {
    }
}
