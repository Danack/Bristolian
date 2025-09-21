<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicInteger;
use Bristolian\Parameters\PropertyType\BasicFloat;
use Bristolian\Parameters\PropertyType\BasicString;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class BristolStairsPositionParams implements DataType
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('bristol_stair_info_id')]
        public readonly string $bristol_stair_info_id,
        #[BasicFloat('latitude')]
        public readonly string $latitude,
        #[BasicFloat('longitude')]
        public readonly float $longitude,
    ) {
    }
}
