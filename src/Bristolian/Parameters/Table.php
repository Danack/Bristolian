<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\Parameters\PropertyType\TableNumberOfRowsValue;
use Bristolian\StaticFactory;
use DataType\Create\CreateArrayOfTypeFromArray;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class Table implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateArrayOfTypeFromArray;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[TableNumberOfRowsValue('TABLE_ROWS')]
        public readonly int $number_of_rows,
        #[BasicString('TABLE_NAME')]
        public readonly string $name,
    ) {
    }
}
