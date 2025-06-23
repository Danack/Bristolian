<?php

namespace Bristolian\DataType;

use Bristolian\DataType\PropertyType\BasicString;
use Bristolian\DataType\PropertyType\SourceLinkPositionValue;
use DataType\Create\CreateArrayOfTypeFromArray;
use DataType\Create\CreateFromArray;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class Table implements DataType
{
    use CreateFromArray;
    use CreateArrayOfTypeFromArray;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[SourceLinkPositionValue('TABLE_ROWS')]
        public readonly int $number_of_rows,
        #[BasicString('TABLE_NAME')]
        public readonly string $name,
    ) {
    }
}
