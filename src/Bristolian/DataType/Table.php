<?php

namespace Bristolian\DataType;

use DataType\Create\CreateFromArray;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Create\CreateArrayOfTypeFromArray;

class Table implements DataType
{
    use CreateFromArray;
    use CreateArrayOfTypeFromArray;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicInteger('TABLE_ROWS')]
        public readonly int $number_of_rows,
        #[BasicString('TABLE_NAME')]
        public readonly string $name,
    ) {
    }
}
