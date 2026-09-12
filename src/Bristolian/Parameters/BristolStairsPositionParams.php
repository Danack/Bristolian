<?php

namespace Bristolian\Parameters;

use DataType\Basic\BasicFloat;
use DataType\Basic\BasicString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * This is used for updating an existing flight of stairs position
 */
class BristolStairsPositionParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('bristol_stair_info_id')]
        public readonly string $bristol_stair_info_id,
        #[BasicFloat('latitude')]
        public readonly float $latitude,
        #[BasicFloat('longitude')]
        public readonly float $longitude,
    ) {
    }
}
